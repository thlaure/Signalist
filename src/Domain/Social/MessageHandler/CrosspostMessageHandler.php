<?php

declare(strict_types=1);

namespace App\Domain\Social\MessageHandler;

use App\Domain\Social\Message\CrosspostMessage;
use App\Domain\Social\Port\CrosspostAdapterInterface;
use App\Entity\Crosspost;

use function count;

use Doctrine\ORM\EntityManagerInterface;

use function in_array;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;

#[AsMessageHandler]
final readonly class CrosspostMessageHandler
{
    public function __construct(private EntityManagerInterface $entityManager, private CrosspostAdapterInterface $adapter, private MessageBusInterface $bus)
    {
    }

    public function __invoke(CrosspostMessage $message): void
    {
        $post = $this->entityManager->find(Crosspost::class, Uuid::fromString($message->crosspostId));

        if (!$post instanceof Crosspost || 'pending' !== $post->getStatus()) {
            return;
        }
        $connection = $this->entityManager->getConnection();
        $key = 'crosspost-'.$message->crosspostId;

        if (!in_array($connection->fetchOne('SELECT pg_try_advisory_lock(hashtextextended(:key, 0))', ['key' => $key]), [true, 1, 't'], true)) {
            return;
        }

        try {
            $this->entityManager->refresh($post);
            $account = $post->getAccount();
            $this->entityManager->refresh($account);
            $owner = $account->getOwner();
            $this->entityManager->refresh($owner);

            if ('pending' !== $post->getStatus()) {
                return;
            }

            if ($owner->isDeleted() || $owner->isProcessingRestricted() || 'connected' !== $account->getStatus() || $account->getRemoteId() !== $post->getRemoteAccountId()) {
                $post->setStatus('cancelled');
                $this->entityManager->flush();

                return;
            }
            // Persist before a non-idempotent remote POST. A lost response must never produce an automatic duplicate.
            $post->setStatus('uncertain');
            $this->entityManager->flush();
            $id = $this->adapter->publish($post, count($post->getPublishedIds()));
            $post->published($id);
            $this->entityManager->flush();

            if ('pending' === $post->getStatus()) {
                $this->bus->dispatch(new CrosspostMessage($message->crosspostId));
            }
        } finally {
            $connection->executeQuery('SELECT pg_advisory_unlock(hashtextextended(:key, 0))', ['key' => $key]);
        }
    }
}
