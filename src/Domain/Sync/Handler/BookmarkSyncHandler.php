<?php

declare(strict_types=1);

namespace App\Domain\Sync\Handler;

use App\Domain\Connection\Handler\ConnectionHandler;
use App\Domain\Sync\Message\SyncRaindropMessage;
use App\Entity\BookmarkSyncRun;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Uid\Uuid;

final readonly class BookmarkSyncHandler
{
    public function __construct(private EntityManagerInterface $entityManager, private ConnectionHandler $connections, private MessageBusInterface $bus)
    {
    }

    public function create(User $user, string $accountId, bool $includeNotes): BookmarkSyncRun
    {
        if ($user->isProcessingRestricted()) {
            throw new AccessDeniedException();
        }
        $account = $this->connections->account($user, $accountId);

        if ('raindrop' !== $account->getProvider() || 'connected' !== $account->getStatus() || null === $account->getRemoteId()) {
            throw new ConflictHttpException('Connect Raindrop before synchronizing.');
        }

        if (null !== $this->entityManager->getRepository(BookmarkSyncRun::class)->findOneBy(['account' => $account, 'status' => 'pending'])) {
            throw new ConflictHttpException('A sync is already running.');
        }
        $run = new BookmarkSyncRun($account, $includeNotes, $account->getRemoteId());
        $this->entityManager->persist($run);
        $this->entityManager->flush();
        $this->bus->dispatch(new SyncRaindropMessage($run->getId()->toRfc4122()));

        return $run;
    }

    /** @return array<string, mixed> */
    public function get(User $user, string $id): array
    {
        $run = Uuid::isValid($id) ? $this->entityManager->find(BookmarkSyncRun::class, Uuid::fromString($id)) : null;

        if (!$run instanceof BookmarkSyncRun || !$run->getAccount()->getOwner()->getId()->equals($user->getId())) {
            throw new NotFoundHttpException();
        }

        return ['id' => $id, 'status' => $run->getStatus(), 'phase' => $run->getPhase(), 'processed' => $run->getProcessed()];
    }
}
