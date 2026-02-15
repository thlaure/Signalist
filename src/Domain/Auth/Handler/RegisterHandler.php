<?php

declare(strict_types=1);

namespace App\Domain\Auth\Handler;

use App\Domain\Auth\Command\RegisterCommand;
use App\Domain\Auth\Exception\EmailAlreadyExistsException;
use App\Domain\Auth\Port\UserRepositoryInterface;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final readonly class RegisterHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function __invoke(RegisterCommand $command): string
    {
        $existingUser = $this->userRepository->findByEmail($command->email);

        if ($existingUser instanceof User) {
            throw new EmailAlreadyExistsException($command->email);
        }

        $user = new User();
        $user->setEmail($command->email);
        $user->setPassword($this->passwordHasher->hashPassword($user, $command->password));

        $this->userRepository->save($user);

        return $user->getId()->toRfc4122();
    }
}
