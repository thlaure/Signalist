<?php

declare(strict_types=1);

namespace App\Tests\Integration\Privacy;

use App\Domain\Privacy\Service\RetentionPolicy;
use App\Entity\Article;
use App\Entity\Bookmark;
use App\Entity\Category;
use App\Entity\Feed;
use App\Entity\Organization;
use App\Entity\SharedBookmark;
use App\Entity\User;
use App\Tests\Integration\DatabaseTestCase;

final class RetentionPolicyTest extends DatabaseTestCase
{
    public function testDryRunPreservesDataAndBothBookmarkKindsProtectOldArticles(): void
    {
        $user = new User()->setEmail('owner@example.com')->setPassword('hash');
        $organization = new Organization('Team');
        $category = new Category()->setOwner($user)->setName('News')->setSlug('news');
        $feed = new Feed()->setOwner($user)->setCategory($category)->setTitle('News')->setUrl('https://example.com/rss');
        foreach ([$user, $organization, $category, $feed] as $entity) {
            $this->entityManager->persist($entity);
        }
        $articles = [];
        foreach (['plain', 'personal', 'shared'] as $guid) {
            $article = new Article()->setFeed($feed)->setGuid($guid)->setTitle($guid)->setUrl('https://example.com/'.$guid);
            $this->entityManager->persist($article);
            $articles[$guid] = $article;
        }
        $this->entityManager->persist(new Bookmark()->setArticle($articles['personal']));
        $this->entityManager->persist(new SharedBookmark($organization, $articles['shared'], $user));
        $this->entityManager->flush();
        $connection = $this->entityManager->getConnection();
        $connection->executeStatement("UPDATE article SET created_at = '2020-01-01 00:00:00'");
        $policy = new RetentionPolicy($connection);
        $this->assertSame(1, $policy->run());
        $this->assertSame(3, $this->entityManager->getRepository(Article::class)->count([]));
        $this->assertSame(1, $policy->run(true));
        $this->entityManager->clear();
        $this->assertNull($this->entityManager->find(Article::class, $articles['plain']->getId()));
        $this->assertInstanceOf(Article::class, $this->entityManager->find(Article::class, $articles['personal']->getId()));
        $this->assertInstanceOf(Article::class, $this->entityManager->find(Article::class, $articles['shared']->getId()));
    }
}
