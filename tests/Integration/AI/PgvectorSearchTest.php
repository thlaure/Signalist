<?php

declare(strict_types=1);

namespace App\Tests\Integration\AI;

use App\Entity\Article;
use App\Entity\ArticleEmbedding;
use App\Entity\Category;
use App\Entity\Feed;
use App\Entity\User;
use App\Infrastructure\Persistence\Search\PgvectorSearchRepository;
use App\Tests\Integration\DatabaseTestCase;

final class PgvectorSearchTest extends DatabaseTestCase
{
    public function testRealCosineSearchFiltersDistanceAndOwner(): void
    {
        $owner = new User()->setEmail('owner@example.com')->setPassword('hash');
        $other = new User()->setEmail('other@example.com')->setPassword('hash');
        $this->entityManager->persist($owner);
        $this->entityManager->persist($other);
        $vector = array_fill(0, 1536, 0.0);
        $vector[0] = 1.0;
        $expected = null;
        foreach ([$owner, $other] as $user) {
            $category = new Category()->setOwner($user)->setName('News')->setSlug('news');
            $feed = new Feed()->setOwner($user)->setCategory($category)->setTitle('News')->setUrl('https://example.com/rss');
            $this->entityManager->persist($category);
            $this->entityManager->persist($feed);
            foreach ([1.0, -1.0] as $direction) {
                $article = new Article()->setFeed($feed)->setGuid((string) $direction)->setTitle($direction > 0 ? 'Useful source' : 'Other topic')->setUrl('https://example.com/'.$direction);
                $embedding = $vector;
                $embedding[0] = $direction;
                $this->entityManager->persist($article);
                $this->entityManager->persist(new ArticleEmbedding()->setArticle($article)->setChunkIndex(0)->setChunkText('Clean source')->setEmbedding($embedding));

                if ($user === $owner && $direction > 0) {
                    $expected = $article->getId()->toRfc4122();
                }
            }
        }
        $this->entityManager->flush();
        $this->assertIsString($expected);
        $repository = new PgvectorSearchRepository($this->entityManager->getConnection());
        $this->assertSame([$expected], $repository->semantic($owner, $vector));
        $this->assertSame([$expected], $repository->fulltext($owner, 'Useful'));
    }
}
