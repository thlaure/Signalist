<?php

declare(strict_types=1);

namespace App\Domain\Auth\Handler;

use App\Domain\Auth\Command\VerifyEmailCommand;
use App\Domain\Auth\Exception\InvalidVerificationTokenException;
use App\Domain\Auth\Port\EmailVerificationTokenGeneratorInterface;
use App\Domain\Auth\Port\UserRepositoryInterface;
use App\Entity\User;
use DateTimeImmutable;

final readonly class VerifyEmailHandler
{
    public function __construct(
        private EmailVerificationTokenGeneratorInterface $tokenGenerator,
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function __invoke(VerifyEmailCommand $command): void
    {
        $isValid = $this->tokenGenerator->validateSignedUrl(
            $command->userId,
            $command->email,
            $command->expiresAt,
            $command->signature,
        );

        if (!$isValid) {
            throw new InvalidVerificationTokenException();
        }

        $user = $this->userRepository->find($command->userId);

        if (!$user instanceof User || $user->getEmail() !== $command->email) {
            throw new InvalidVerificationTokenException();
        }

        if ($user->isEmailVerified()) {
            return;
        }

        $user->setEmailVerifiedAt(new DateTimeImmutable());
        $this->userRepository->save($user);
    }
}
