<?php

declare(strict_types=1);

namespace App\Domain\Auth\Handler;

use App\Domain\Auth\Command\LoginCommand;
use App\Domain\Auth\Exception\EmailNotVerifiedException;
use App\Domain\Auth\Exception\InvalidCredentialsException;
use App\Domain\Auth\Port\UserRepositoryInterface;
use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final readonly class LoginHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private JWTTokenManagerInterface $jwtManager,
    ) {
    }

    /**
     * @return array{token: string, expiresIn: int}
     */
    public function __invoke(LoginCommand $command): array
    {
        $user = $this->userRepository->findByEmail($command->email);

        if (!$user instanceof User || $user->isDeleted()) {
            throw new InvalidCredentialsException();
        }

        if (!$this->passwordHasher->isPasswordValid($user, $command->password)) {
            throw new InvalidCredentialsException();
        }

        if (!$user->isEmailVerified()) {
            throw new EmailNotVerifiedException();
        }

        $token = $this->jwtManager->create($user);

        return [
            'token' => $token,
            'expiresIn' => 3600,
        ];
    }
}
