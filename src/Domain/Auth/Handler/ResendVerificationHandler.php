<?php

declare(strict_types=1);

namespace App\Domain\Auth\Handler;

use App\Domain\Auth\Command\ResendVerificationCommand;
use App\Domain\Auth\Message\SendVerificationEmailMessage;
use App\Domain\Auth\Port\UserRepositoryInterface;
use App\Entity\User;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class ResendVerificationHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private MessageBusInterface $messageBus,
    ) {
    }

    public function __invoke(ResendVerificationCommand $command): void
    {
        $user = $this->userRepository->findByEmail($command->email);

        if (!$user instanceof User || $user->isDeleted() || $user->isEmailVerified()) {
            return;
        }

        $this->messageBus->dispatch(new SendVerificationEmailMessage(
            userId: $user->getId()->toRfc4122(),
            email: $user->getEmail(),
        ));
    }
}
