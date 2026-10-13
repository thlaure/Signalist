<?php

declare(strict_types=1);

namespace App\Tests\Integration\Tag;

use App\Domain\Tag\Handler\TagHandler;
use App\Domain\Tag\Service\TagCatalog;
use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Feed;
use App\Entity\Tag;
use App\Entity\User;
use App\Tests\Integration\DatabaseTestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class TagWorkflowTest extends DatabaseTestCase
{
    public function testAssignFilterRenameAndDeleteAreOwnerScoped(): void
    {
        $owner = new User()->setEmail('owner@example.com')->setPassword('hash');
        $other = new User()->setEmail('other@example.com')->setPassword('hash');
        $category = new Category()->setOwner($owner)->setName('News')->setSlug('news');
        $feed = new Feed()->setOwner($owner)->setCategory($category)->setTitle('News')->setUrl('https://example.com/rss');
        $article = new Article()->setFeed($feed)->setTitle('Tagged source')->setGuid('guid')->setUrl('https://example.com/article');
        foreach ([$owner, $other, $category, $feed, $article] as $entity) {
            $this->entityManager->persist($entity);
        } $this->entityManager->flush();
        $handler = new TagHandler($this->entityManager, new TagCatalog($this->entityManager));
        $handler->assign($owner, $article->getId()->toRfc4122(), ['Tech', 'tech', 'Science']);
        $this->assertSame(['Tech', 'Science'], $article->getTags());
        $tag = array_first(array_filter($handler->list($owner), static fn ($tag): bool => 'Tech' === $tag->getName()));
        $this->assertInstanceOf(Tag::class, $tag);
        $this->assertCount(1, $handler->articles($owner, $tag->getId()->toRfc4122()));
        $this->assertSame([], $handler->list($other));
        $handler->save($owner, 'Technology', $tag->getId()->toRfc4122());
        $this->assertContains('Technology', $article->getTags());
        $this->assertNotContains('Tech', $article->getTags());
        $handler->delete($owner, $tag->getId()->toRfc4122());
        $this->assertSame(['Science'], $article->getTags());
        $this->expectException(NotFoundHttpException::class);
        $handler->assign($other, $article->getId()->toRfc4122(), ['Hidden']);
    }
}
