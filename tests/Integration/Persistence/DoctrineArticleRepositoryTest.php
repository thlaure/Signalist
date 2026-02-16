<?php

declare(strict_types=1);

namespace App\Tests\Integration\Persistence;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Feed;
use App\Entity\User;
use App\Infrastructure\Persistence\Article\DoctrineArticleRepository;
use App\Tests\Integration\DatabaseTestCase;
use DateTimeImmutable;

final class DoctrineArticleRepositoryTest extends DatabaseTestCase
{
    private DoctrineArticleRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new DoctrineArticleRepository($this->entityManager);
    }

    public function testSaveAndFind(): void
    {
        [$owner, , $feed] = $this->createFeedGraph();
        $article = $this->createArticle('Test Article', $feed);

        $this->repository->save($article);

        $found = $this->repository->find($article->getId()->toRfc4122());

        $this->assertNotNull($found);
        $this->assertSame('Test Article', $found->getTitle());
    }

    public function testFindReturnsNullForInvalidUuid(): void
    {
        $this->assertNull($this->repository->find('invalid'));
    }

    public function testFindAllWithOwnerFilter(): void
    {
        [$owner1, , $feed1] = $this->createFeedGraph('user1@test.com');
        [$owner2, , $feed2] = $this->createFeedGraph('user2@test.com');

        $this->repository->save($this->createArticle('Article 1', $feed1));
        $this->repository->save($this->createArticle('Article 2', $feed2));

        $result = $this->repository->findAll(['ownerId' => $owner1->getId()->toRfc4122()]);

        $this->assertCount(1, $result);
        $this->assertSame('Article 1', $result[0]->getTitle());
    }

    public function testFindAllWithFeedFilter(): void
    {
        [$owner, $category, $feed1] = $this->createFeedGraph();
        $feed2 = $this->createFeed('Feed 2', 'https://b.com/feed', $category, $owner);

        $this->repository->save($this->createArticle('Article 1', $feed1));
        $this->repository->save($this->createArticle('Article 2', $feed2));

        $result = $this->repository->findAll([
            'ownerId' => $owner->getId()->toRfc4122(),
            'feedId' => $feed1->getId()->toRfc4122(),
        ]);

        $this->assertCount(1, $result);
        $this->assertSame('Article 1', $result[0]->getTitle());
    }

    public function testFindAllWithCategoryFilter(): void
    {
        [$owner, $cat1, $feed1] = $this->createFeedGraph();
        $cat2 = new Category();
        $cat2->setName('Biz');
        $cat2->setSlug('biz');
        $cat2->setOwner($owner);
        $this->entityManager->persist($cat2);
        $feed2 = $this->createFeed('Feed 2', 'https://b.com/feed', $cat2, $owner);

        $this->repository->save($this->createArticle('Article 1', $feed1));
        $this->repository->save($this->createArticle('Article 2', $feed2));

        $result = $this->repository->findAll([
            'ownerId' => $owner->getId()->toRfc4122(),
            'categoryId' => $cat1->getId()->toRfc4122(),
        ]);

        $this->assertCount(1, $result);
        $this->assertSame('Article 1', $result[0]->getTitle());
    }

    public function testFindAllWithIsReadFilter(): void
    {
        [$owner, , $feed] = $this->createFeedGraph();
        $read = $this->createArticle('Read Article', $feed);
        $read->setIsRead(true);
        $unread = $this->createArticle('Unread Article', $feed);
        $unread->setIsRead(false);

        $this->repository->save($read);
        $this->repository->save($unread);

        $result = $this->repository->findAll([
            'ownerId' => $owner->getId()->toRfc4122(),
            'isRead' => false,
        ]);

        $this->assertCount(1, $result);
        $this->assertSame('Unread Article', $result[0]->getTitle());
    }

    public function testFindAllWithSearchFilter(): void
    {
        [$owner, , $feed] = $this->createFeedGraph();
        $this->repository->save($this->createArticle('CSS Grid Guide', $feed));
        $this->repository->save($this->createArticle('JavaScript Tips', $feed));

        $result = $this->repository->findAll([
            'ownerId' => $owner->getId()->toRfc4122(),
            'search' => 'css',
        ]);

        $this->assertCount(1, $result);
        $this->assertSame('CSS Grid Guide', $result[0]->getTitle());
    }

    public function testFindByFeed(): void
    {
        [, , $feed] = $this->createFeedGraph();
        $this->repository->save($this->createArticle('Article 1', $feed));
        $this->repository->save($this->createArticle('Article 2', $feed));

        $result = $this->repository->findByFeed($feed->getId()->toRfc4122());

        $this->assertCount(2, $result);
    }

    public function testFindUnreadByOwner(): void
    {
        [$owner, , $feed] = $this->createFeedGraph();
        $read = $this->createArticle('Read', $feed);
        $read->setIsRead(true);
        $unread = $this->createArticle('Unread', $feed);
        $unread->setIsRead(false);
        $this->repository->save($read);
        $this->repository->save($unread);

        $result = $this->repository->findUnreadByOwner($owner->getId()->toRfc4122());

        $this->assertCount(1, $result);
        $this->assertSame('Unread', $result[0]->getTitle());
    }

    /**
     * @return array{User, Category, Feed}
     */
    private function createFeedGraph(string $email = 'test@example.com'): array
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
        $feed->setTitle('Test Feed');
        $feed->setUrl('https://example.com/feed-' . bin2hex(random_bytes(4)));
        $feed->setCategory($category);
        $feed->setOwner($user);
        $this->entityManager->persist($feed);

        $this->entityManager->flush();

        return [$user, $category, $feed];
    }

    private function createFeed(string $title, string $url, Category $category, User $owner): Feed
    {
        $feed = new Feed();
        $feed->setTitle($title);
        $feed->setUrl($url);
        $feed->setCategory($category);
        $feed->setOwner($owner);
        $this->entityManager->persist($feed);
        $this->entityManager->flush();

        return $feed;
    }

    private function createArticle(string $title, Feed $feed): Article
    {
        $article = new Article();
        $article->setTitle($title);
        $article->setGuid('guid-' . bin2hex(random_bytes(8)));
        $article->setUrl('https://example.com/' . bin2hex(random_bytes(4)));
        $article->setSummary('Summary of ' . $title);
        $article->setPublishedAt(new DateTimeImmutable());
        $article->setFeed($feed);

        return $article;
    }
}
