<?php

declare(strict_types=1);

namespace App\Domain\Connection\MessageHandler;

use App\Domain\Connection\Message\RefreshAccountMessage;
use App\Domain\Connection\Service\AccountTokens;
use App\Entity\ExternalAccount;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Uid\Uuid;

#[AsMessageHandler]
final readonly class RefreshAccountMessageHandler
{
    public function __construct(private EntityManagerInterface $entityManager, private AccountTokens $tokens)
    {
    }

    public function __invoke(RefreshAccountMessage $message): void
    {
        $account = $this->entityManager->find(ExternalAccount::class, Uuid::fromString($message->accountId));

        if ($account instanceof ExternalAccount && 'connected' === $account->getStatus() && 'bluesky' !== $account->getProvider()) {
            $this->tokens->accessToken($account);
        }
    }
}
