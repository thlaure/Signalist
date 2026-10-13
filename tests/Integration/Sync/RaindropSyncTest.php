<?php

declare(strict_types=1);

namespace App\Tests\Integration\Sync;

use App\Domain\Billing\Service\PlanCatalog;
use App\Domain\Billing\Service\UsageBudget;
use App\Domain\Connection\DTO\OAuthTokens;
use App\Domain\Connection\Port\OAuthGatewayInterface;
use App\Domain\Connection\Service\AccountTokens;
use App\Domain\Sync\DTO\RemoteBookmark;
use App\Domain\Sync\Message\SyncRaindropMessage;
use App\Domain\Sync\MessageHandler\SyncRaindropMessageHandler;
use App\Domain\Sync\Port\RaindropClientInterface;
use App\Domain\Sync\Service\SyncConflictResolver;
use App\Entity\Article;
use App\Entity\Bookmark;
use App\Entity\BookmarkSyncRun;
use App\Entity\Category;
use App\Entity\ExternalAccount;
use App\Entity\Feed;
use App\Entity\User;
use App\Infrastructure\Security\TokenCipher;
use App\Tests\Integration\DatabaseTestCase;
use DateTimeImmutable;
use Symfony\Component\Messenger\MessageBusInterface;

final class RaindropSyncTest extends DatabaseTestCase
{
    public function testImportExportNotesConflictAndNoDuplicateCreates(): void
    {
        $owner = new User()->setEmail('owner@example.com')->setPassword('hash');
        $other = new User()->setEmail('other@example.com')->setPassword('hash');
        $cipher = new TokenCipher(base64_encode(random_bytes(32)));
        $account = new ExternalAccount($owner, 'raindrop');
        $account->connected($cipher->encrypt(new OAuthTokens('token', null, new DateTimeImmutable('+1 hour'), 'remote')->serialize()), new DateTimeImmutable('+1 hour'), 'remote');
        foreach ([$owner, $other, $account] as $entity) {
            $this->entityManager->persist($entity);
        }
        foreach ([$owner, $other] as $user) {
            $category = new Category()->setOwner($user)->setName('News')->setSlug('news');
            $feed = new Feed()->setOwner($user)->setCategory($category)->setUrl('https://example.com/feed')->setTitle('News');
            $article = new Article()->setFeed($feed)->setGuid('guid')->setTitle($user === $owner ? 'Local' : 'Private other')->setUrl('https://example.com/'.($user === $owner ? 'local' : 'other'));
            $bookmark = new Bookmark()->setArticle($article)->setNotes('private-local-note');
            foreach ([$category, $feed, $article, $bookmark] as $entity) {
                $this->entityManager->persist($entity);
            }
        }
        $run = new BookmarkSyncRun($account, false, 'remote');
        $this->entityManager->persist($run);
        $this->entityManager->flush();
        $remote = $this->createMock(RaindropClientInterface::class);
        $remote->method('page')->willReturn([new RemoteBookmark('remote-1', 'https://example.com/imported', 'Imported', 'private-remote-note'), new RemoteBookmark('unsafe', 'https://password@example.com/unsafe', 'Unsafe', null)]);
        $remote->expects($this->once())->method('create')->with('token', 'https://example.com/local', 'Local', null)->willReturn(new RemoteBookmark('local-export', 'https://example.com/local', 'Local', null));
        $remote->expects($this->never())->method('update');
        $worker = $this->worker($cipher, $remote);
        $message = new SyncRaindropMessage($run->getId()->toRfc4122());
        $worker($message);
        $worker($message);
        $worker($message);
        $this->assertSame('complete', $run->getStatus());
        $this->assertSame(3, $this->entityManager->getRepository(Bookmark::class)->count([]));
        $imported = $this->entityManager->getRepository(Article::class)->findOneBy(['url' => 'https://example.com/imported']);
        $this->assertInstanceOf(Article::class, $imported);
        $this->assertSame('raindrop', $imported->getFeed()->getSource());
        $this->assertNull($this->entityManager->getRepository(Bookmark::class)->findOneBy(['article' => $imported])?->getNotes());
        $this->assertNull($this->entityManager->getRepository(Article::class)->findOneBy(['url' => 'https://password@example.com/unsafe']));
    }

    private function worker(TokenCipher $cipher, RaindropClientInterface $remote): SyncRaindropMessageHandler
    {
        return new SyncRaindropMessageHandler($this->entityManager, new AccountTokens($this->entityManager, $this->createMock(OAuthGatewayInterface::class), $cipher), $remote, new SyncConflictResolver(), self::getContainer()->get(MessageBusInterface::class), new UsageBudget($this->entityManager->getConnection(), new PlanCatalog()));
    }
}
