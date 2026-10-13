<?php

declare(strict_types=1);

namespace App\Domain\Tag\Handler;

use App\Domain\Article\DTO\Output\ArticleOutput;
use App\Domain\Tag\Service\TagCatalog;
use App\Entity\Article;
use App\Entity\Organization;
use App\Entity\Tag;
use App\Entity\User;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;

use const JSON_THROW_ON_ERROR;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Uid\Uuid;

final readonly class TagHandler
{
    public function __construct(private EntityManagerInterface $entityManager, private TagCatalog $catalog)
    {
    }

    /** @return list<Tag> */
    public function list(User $user): array
    {
        return $this->entityManager->getRepository(Tag::class)->findBy(['owner' => $user], ['name' => 'ASC'], 1000);
    }

    public function get(User $user, string $id): Tag
    {
        $tag = Uuid::isValid($id) ? $this->entityManager->find(Tag::class, Uuid::fromString($id)) : null;

        if (!$tag instanceof Tag || !$tag->getOwner()->getId()->equals($user->getId())) {
            throw new NotFoundHttpException();
        }

        return $tag;
    }

    public function save(User $user, string $name, ?string $id = null): Tag
    {
        $this->active($user);
        $name = trim($name);

        if ('' === $name || mb_strlen($name) > 60) {
            throw new BadRequestHttpException('Tag name must contain 1 to 60 characters.');
        }

        return $this->entityManager->wrapInTransaction(function () use ($user, $name, $id): Tag {
            $this->entityManager->refresh($user, LockMode::PESSIMISTIC_WRITE);
            $existing = $this->entityManager->getRepository(Tag::class)->findOneBy(['owner' => $user, 'nameKey' => mb_strtolower($name)]);
            $tag = null === $id ? new Tag($user, $name) : $this->get($user, $id);

            if ($existing instanceof Tag && !$existing->getId()->equals($tag->getId())) {
                throw new ConflictHttpException('Tag already exists.');
            }
            $old = $tag->getName();
            $tag->rename($name);
            $this->entityManager->persist($tag);

            if (null !== $id) {
                $this->replaceArticleNames($user, $old, $name);
            }

            return $tag;
        });
    }

    public function delete(User $user, string $id): void
    {
        $this->active($user);
        $this->entityManager->wrapInTransaction(function () use ($user, $id): void {
            $this->entityManager->refresh($user, LockMode::PESSIMISTIC_WRITE);
            $tag = $this->get($user, $id);
            $this->replaceArticleNames($user, $tag->getName(), null);
            $this->entityManager->remove($tag);
        });
    }

    /** @param list<string> $names */
    public function assign(User $user, string $articleId, array $names): void
    {
        $this->active($user);
        $article = Uuid::isValid($articleId) ? $this->entityManager->find(Article::class, Uuid::fromString($articleId)) : null;

        if (!$article instanceof Article || $article->getFeed()->getOrganization() instanceof Organization || !$article->getFeed()->getOwner()->getId()->equals($user->getId())) {
            throw new NotFoundHttpException();
        }
        $this->entityManager->wrapInTransaction(function () use ($article, $user, $names): void { $article->setTags($this->catalog->register($user, $names)); });
    }

    /** @return list<ArticleOutput> */
    public function articles(User $user, string $id): array
    {
        $tag = $this->get($user, $id);

        return array_map(ArticleOutput::fromEntity(...), $this->matchingArticles($user, $tag->getName(), 200));
    }

    /** @return array{id: string, name: string} */
    public function output(Tag $tag): array
    {
        return ['id' => $tag->getId()->toRfc4122(), 'name' => $tag->getName()];
    }

    private function active(User $user): void
    {
        if ($user->isDeleted() || $user->isProcessingRestricted()) {
            throw new AccessDeniedException();
        }
    }

    private function replaceArticleNames(User $user, string $old, ?string $name): void
    {
        foreach ($this->matchingArticles($user, $old) as $article) {
            $tags = array_values(array_filter($article->getTags(), static fn (string $tag): bool => $tag !== $old));

            if (null !== $name) {
                $tags[] = $name;
            } $article->setTags(array_values(array_unique($tags)));
        }
    }

    /** @return list<Article> */
    private function matchingArticles(User $user, string $name, ?int $limit = null): array
    {
        $ids = $this->entityManager->getConnection()->fetchFirstColumn('SELECT a.id FROM article a JOIN feed f ON a.feed_id = f.id WHERE f.owner_id = :owner AND f.organization_id IS NULL AND a.tags::jsonb @> CAST(:tag AS jsonb) ORDER BY a.created_at DESC'.(null === $limit ? '' : ' LIMIT '.$limit), ['owner' => $user->getId()->toRfc4122(), 'tag' => json_encode([$name], JSON_THROW_ON_ERROR)]);

        return [] === $ids ? [] : $this->entityManager->getRepository(Article::class)->findBy(['id' => $ids], ['createdAt' => 'DESC']);
    }
}
