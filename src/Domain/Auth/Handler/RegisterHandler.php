<?php

declare(strict_types=1);

namespace App\Domain\Auth\Handler;

use App\Domain\Auth\Command\RegisterCommand;
use App\Domain\Auth\Message\SendVerificationEmailMessage;
use App\Domain\Auth\Port\UserRepositoryInterface;
use App\Entity\User;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final readonly class RegisterHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private MessageBusInterface $messageBus,
    ) {
    }

    public function __invoke(RegisterCommand $command): void
    {
        $existingUser = $this->userRepository->findByEmail($command->email);

        if ($existingUser instanceof User) {
            // Silently ignore — do not reveal whether the email is registered.
            // The caller always receives the same generic 201 response.
            return;
        }

        $user = new User();
        $user->setEmail($command->email);
        $user->setPassword($this->passwordHasher->hashPassword($user, $command->password));

        $this->userRepository->save($user);

        $this->messageBus->dispatch(new SendVerificationEmailMessage(
            userId: $user->getId()->toRfc4122(),
            email: $user->getEmail(),
        ));
    }
}
