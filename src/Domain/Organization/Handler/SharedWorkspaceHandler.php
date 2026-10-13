<?php

declare(strict_types=1);

namespace App\Domain\Organization\Handler;

use App\Domain\Article\DTO\Output\ArticleOutput;
use App\Domain\Billing\Exception\PlanLimitReachedException;
use App\Domain\Billing\Service\PlanCatalog;
use App\Domain\Category\DTO\Input\CreateCategoryInput;
use App\Domain\Category\DTO\Input\UpdateCategoryInput;
use App\Domain\Category\DTO\Output\CategoryOutput;
use App\Domain\Feed\DTO\Input\AddFeedInput;
use App\Domain\Feed\DTO\Input\UpdateFeedInput;
use App\Domain\Feed\DTO\Output\FeedOutput;
use App\Domain\Feed\Message\CrawlFeedMessage;
use App\Entity\Article;
use App\Entity\ArticleReadState;
use App\Entity\BookmarkAnnotation;
use App\Entity\Category;
use App\Entity\Feed;
use App\Entity\Organization;
use App\Entity\SharedBookmark;
use App\Entity\User;

use const DATE_ATOM;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;

final readonly class SharedWorkspaceHandler
{
    public function __construct(private EntityManagerInterface $entityManager, private OrganizationHandler $organizations, private MessageBusInterface $bus, private PlanCatalog $plans = new PlanCatalog())
    {
    }

    /** @return list<CategoryOutput> */
    public function categories(User $user, string $organizationId): array
    {
        $organization = $this->organizations->get($user, $organizationId);

        return array_values(array_map(CategoryOutput::fromEntity(...), $this->entityManager->getRepository(Category::class)->findBy(['organization' => $organization], ['name' => 'ASC'])));
    }

    public function createCategory(User $user, string $organizationId, CreateCategoryInput $input): Category
    {
        $organization = $this->organizations->get($user, $organizationId, 'write');

        if (null !== $this->entityManager->getRepository(Category::class)->findOneBy(['organization' => $organization, 'slug' => $input->slug])) {
            throw new ConflictHttpException('Category slug already exists.');
        }
        $category = new Category()->setName($input->name)->setSlug($input->slug)->setColor($input->color)->setDescription($input->description)->setPosition($input->position)->setOwner($user)->setOrganization($organization);
        $this->entityManager->persist($category);
        $this->organizations->activity($organization, $user, 'category_created');

        return $category;
    }

    public function updateCategory(User $user, string $organizationId, string $categoryId, UpdateCategoryInput $input): Category
    {
        $organization = $this->organizations->get($user, $organizationId, 'write');
        $category = $this->category($organization, $categoryId);
        $existing = $this->entityManager->getRepository(Category::class)->findOneBy(['organization' => $organization, 'slug' => $input->slug]);

        if ($existing instanceof Category && !$existing->getId()->equals($category->getId())) {
            throw new ConflictHttpException('Category slug already exists.');
        }
        $category->setName($input->name)->setSlug($input->slug)->setDescription($input->description)->setColor($input->color)->setPosition($input->position);
        $this->organizations->activity($organization, $user, 'category_updated');

        return $category;
    }

    public function deleteCategory(User $user, string $organizationId, string $categoryId): void
    {
        $organization = $this->organizations->get($user, $organizationId, 'write');
        $category = $this->category($organization, $categoryId);

        if ($this->entityManager->getRepository(Feed::class)->count(['category' => $category]) > 0) {
            throw new ConflictHttpException('Remove feeds before deleting this category.');
        }
        $this->entityManager->remove($category);
        $this->organizations->activity($organization, $user, 'category_deleted');
    }

    /** @return list<FeedOutput> */
    public function feeds(User $user, string $organizationId): array
    {
        $organization = $this->organizations->get($user, $organizationId);

        return array_values(array_map(FeedOutput::fromEntity(...), $this->entityManager->getRepository(Feed::class)->findBy(['organization' => $organization], ['title' => 'ASC'])));
    }

    public function createFeed(User $user, string $organizationId, AddFeedInput $input): Feed
    {
        $organization = $this->organizations->get($user, $organizationId, 'write');
        $limit = $this->plans->limits($organization->getPlan())['feeds'];

        if ($limit >= 0 && $this->entityManager->getRepository(Feed::class)->count(['organization' => $organization]) >= $limit) {
            throw new PlanLimitReachedException('feeds');
        }
        $category = $this->category($organization, $input->categoryId);

        if (null !== $this->entityManager->getRepository(Feed::class)->findOneBy(['organization' => $organization, 'url' => $input->url])) {
            throw new ConflictHttpException('Feed URL already exists.');
        }
        $feed = new Feed()->setUrl($input->url)->setTitle($input->title ?? $input->url)->setCategory($category)->setOwner($user);
        $this->entityManager->persist($feed);
        $this->organizations->activity($organization, $user, 'feed_created');
        $this->bus->dispatch(new CrawlFeedMessage($feed->getId()->toRfc4122()));

        return $feed;
    }

    public function updateFeed(User $user, string $organizationId, string $feedId, UpdateFeedInput $input): Feed
    {
        $organization = $this->organizations->get($user, $organizationId, 'write');
        $feed = Uuid::isValid($feedId) ? $this->entityManager->find(Feed::class, Uuid::fromString($feedId)) : null;

        if (!$feed instanceof Feed || !$feed->getOrganization()?->getId()->equals($organization->getId())) {
            throw new NotFoundHttpException();
        }
        $feed->setTitle($input->title)->setCategory($this->category($organization, $input->categoryId))->setStatus($input->status);
        $this->organizations->activity($organization, $user, 'feed_updated');

        return $feed;
    }

    public function deleteFeed(User $user, string $organizationId, string $feedId): void
    {
        $organization = $this->organizations->get($user, $organizationId, 'write');
        $feed = Uuid::isValid($feedId) ? $this->entityManager->find(Feed::class, Uuid::fromString($feedId)) : null;

        if (!$feed instanceof Feed || !$feed->getOrganization()?->getId()->equals($organization->getId())) {
            throw new NotFoundHttpException();
        }
        $this->entityManager->remove($feed);
        $this->organizations->activity($organization, $user, 'feed_deleted');
    }

    /** @return list<array<string, mixed>> */
    public function articles(User $user, string $organizationId, int $page, string $search): array
    {
        $organization = $this->organizations->get($user, $organizationId);
        $qb = $this->entityManager->createQueryBuilder()->select('a')->from(Article::class, 'a')->join('a.feed', 'f')->where('f.organization = :organization')->setParameter('organization', $organization)
            ->orderBy('a.createdAt', 'DESC')->setFirstResult((max(1, $page) - 1) * 20)->setMaxResults(20);

        if ('' !== $search) {
            $qb->andWhere('LOWER(a.title) LIKE :search')->setParameter('search', '%'.mb_strtolower(mb_substr($search, 0, 500)).'%');
        }
        /** @var list<Article> $articles */ $articles = $qb->getQuery()->getResult();

        return array_map(fn (Article $article): array => $this->articleOutput($user, $article), $articles);
    }

    public function article(User $user, string $organizationId, string $articleId): Article
    {
        $organization = $this->organizations->get($user, $organizationId);
        $article = Uuid::isValid($articleId) ? $this->entityManager->find(Article::class, Uuid::fromString($articleId)) : null;

        if (!$article instanceof Article || !$article->getFeed()->getOrganization()?->getId()->equals($organization->getId())) {
            throw new NotFoundHttpException();
        }

        return $article;
    }

    /** @return array<string, mixed> */
    public function articleOutput(User $user, Article $article): array
    {
        $state = $this->entityManager->getRepository(ArticleReadState::class)->findOneBy(['user' => $user, 'article' => $article]);

        return array_replace(get_object_vars(ArticleOutput::fromEntity($article)), ['isRead' => $state?->isRead() ?? false]);
    }

    public function markRead(User $user, string $organizationId, string $articleId, bool $read): void
    {
        $article = $this->article($user, $organizationId, $articleId);
        $state = $this->entityManager->getRepository(ArticleReadState::class)->findOneBy(['user' => $user, 'article' => $article]);

        if (!$state instanceof ArticleReadState) {
            $state = new ArticleReadState($article, $user, $read);
            $this->entityManager->persist($state);
        } else {
            $state->setIsRead($read);
        }
        $this->entityManager->flush();
    }

    public function bookmark(User $user, string $organizationId, string $articleId): SharedBookmark
    {
        $organization = $this->organizations->get($user, $organizationId, 'write');
        $article = $this->article($user, $organizationId, $articleId);
        $existing = $this->entityManager->getRepository(SharedBookmark::class)->findOneBy(['organization' => $organization, 'article' => $article]);

        if ($existing instanceof SharedBookmark) {
            return $existing;
        }
        $bookmark = new SharedBookmark($organization, $article, $user);
        $this->entityManager->persist($bookmark);
        $this->organizations->activity($organization, $user, 'article_curated');

        return $bookmark;
    }

    /** @return list<array<string, mixed>> */
    public function bookmarks(User $user, string $organizationId): array
    {
        $organization = $this->organizations->get($user, $organizationId);
        $bookmarks = $this->entityManager->getRepository(SharedBookmark::class)->findBy(['organization' => $organization], ['createdAt' => 'DESC'], 100);

        return array_values(array_map(function (SharedBookmark $bookmark) use ($user): array {
            $annotations = $this->entityManager->getRepository(BookmarkAnnotation::class)->findBy(['bookmark' => $bookmark], ['createdAt' => 'ASC'], 100);

            return ['id' => $bookmark->getId()->toRfc4122(), 'article' => $this->articleOutput($user, $bookmark->getArticle()), 'curator' => $bookmark->getCurator()?->getDisplayName(),
                'annotations' => array_map(static fn (BookmarkAnnotation $annotation): array => ['id' => $annotation->getId()->toRfc4122(), 'body' => $annotation->getBody(), 'author' => $annotation->getAuthor()?->getDisplayName(), 'createdAt' => $annotation->getCreatedAt()->format(DATE_ATOM)], $annotations)];
        }, $bookmarks));
    }

    public function annotate(User $user, string $organizationId, string $bookmarkId, string $body): void
    {
        $organization = $this->organizations->get($user, $organizationId, 'write');
        $bookmark = $this->sharedBookmark($organization, $bookmarkId);
        $this->entityManager->persist(new BookmarkAnnotation($bookmark, $user, trim($body)));
        $this->organizations->activity($organization, $user, 'bookmark_annotated');
    }

    public function removeBookmark(User $user, string $organizationId, string $bookmarkId): void
    {
        $organization = $this->organizations->get($user, $organizationId, 'write');
        $this->entityManager->remove($this->sharedBookmark($organization, $bookmarkId));
        $this->organizations->activity($organization, $user, 'bookmark_removed');
    }

    private function category(Organization $organization, string $id): Category
    {
        $category = Uuid::isValid($id) ? $this->entityManager->find(Category::class, Uuid::fromString($id)) : null;

        if (!$category instanceof Category || !$category->getOrganization()?->getId()->equals($organization->getId())) {
            throw new NotFoundHttpException();
        }

        return $category;
    }

    private function sharedBookmark(Organization $organization, string $id): SharedBookmark
    {
        $bookmark = Uuid::isValid($id) ? $this->entityManager->find(SharedBookmark::class, Uuid::fromString($id)) : null;

        if (!$bookmark instanceof SharedBookmark || !$bookmark->getOrganization()->getId()->equals($organization->getId())) {
            throw new NotFoundHttpException();
        }

        return $bookmark;
    }
}
