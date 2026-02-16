<?php

declare(strict_types=1);

namespace App\Tests\Integration\Persistence;

use App\Entity\Article;
use App\Entity\Bookmark;
use App\Entity\Category;
use App\Entity\Feed;
use App\Entity\User;
use App\Infrastructure\Persistence\Bookmark\DoctrineBookmarkRepository;
use App\Tests\Integration\DatabaseTestCase;
use DateTimeImmutable;

final class DoctrineBookmarkRepositoryTest extends DatabaseTestCase
{
    private DoctrineBookmarkRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new DoctrineBookmarkRepository($this->entityManager);
    }

    public function testSaveAndFind(): void
    {
        [$owner, $article] = $this->createArticleGraph();
        $bookmark = new Bookmark();
        $bookmark->setArticle($article);
        $bookmark->setNotes('Great article');

        $this->repository->save($bookmark);

        $found = $this->repository->find($bookmark->getId()->toRfc4122());

        $this->assertNotNull($found);
        $this->assertSame('Great article', $found->getNotes());
    }

    public function testFindReturnsNullForInvalidUuid(): void
    {
        $this->assertNull($this->repository->find('invalid'));
    }

    public function testFindByArticle(): void
    {
        [$owner, $article] = $this->createArticleGraph();
        $bookmark = new Bookmark();
        $bookmark->setArticle($article);
        $this->repository->save($bookmark);

        $found = $this->repository->findByArticle($article->getId()->toRfc4122());

        $this->assertNotNull($found);
        $this->assertSame($bookmark->getId()->toRfc4122(), $found->getId()->toRfc4122());
    }

    public function testFindByArticleReturnsNullWhenNotBookmarked(): void
    {
        [$owner, $article] = $this->createArticleGraph();

        $this->assertNull($this->repository->findByArticle($article->getId()->toRfc4122()));
    }

    public function testFindAllByOwner(): void
    {
        [$owner1, $article1] = $this->createArticleGraph('user1@test.com');
        [$owner2, $article2] = $this->createArticleGraph('user2@test.com');

        $bm1 = new Bookmark();
        $bm1->setArticle($article1);
        $this->repository->save($bm1);

        $bm2 = new Bookmark();
        $bm2->setArticle($article2);
        $this->repository->save($bm2);

        $result = $this->repository->findAllByOwner($owner1->getId()->toRfc4122());

        $this->assertCount(1, $result);
    }

    public function testDelete(): void
    {
        [$owner, $article] = $this->createArticleGraph();
        $bookmark = new Bookmark();
        $bookmark->setArticle($article);
        $this->repository->save($bookmark);

        $this->repository->delete($bookmark);

        $this->assertNull($this->repository->find($bookmark->getId()->toRfc4122()));
    }

    /**
     * @return array{User, Article}
     */
    private function createArticleGraph(string $email = 'test@example.com'): array
    {
        $user = new User();
        $user->setEmail($email);
        $user->setPassword('hashed');
        $this->entityManager->persist($user);

        $category = new Category();
        $category->setName('Tech');
        $category->setSlug('tech-' . bin2hex(random_bytes(4)));
        $category->setOwner($user);
        $this->entityManager->persist($category);

        $feed = new Feed();
        $feed->setTitle('Feed');
        $feed->setUrl('https://example.com/feed-' . bin2hex(random_bytes(4)));
        $feed->setCategory($category);
        $feed->setOwner($user);
        $this->entityManager->persist($feed);

        $article = new Article();
        $article->setTitle('Article');
        $article->setGuid('guid-' . bin2hex(random_bytes(8)));
        $article->setUrl('https://example.com/' . bin2hex(random_bytes(4)));
        $article->setPublishedAt(new DateTimeImmutable());
        $article->setFeed($feed);
        $this->entityManager->persist($article);

        $this->entityManager->flush();

        return [$user, $article];
    }
}
