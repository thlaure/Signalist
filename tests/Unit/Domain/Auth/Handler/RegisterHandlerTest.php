<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Auth\Handler;

use App\Domain\Auth\Command\RegisterCommand;
use App\Domain\Auth\Exception\EmailAlreadyExistsException;
use App\Domain\Auth\Handler\RegisterHandler;
use App\Domain\Auth\Port\UserRepositoryInterface;
use App\Entity\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Uuid;

final class RegisterHandlerTest extends TestCase
{
    private UserRepositoryInterface&MockObject $userRepository;

    private UserPasswordHasherInterface&MockObject $passwordHasher;

    private RegisterHandler $handler;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);

        $this->handler = new RegisterHandler(
            $this->userRepository,
            $this->passwordHasher,
        );
    }

    public function testInvokeWithValidDataCreatesUserAndReturnsId(): void
    {
        $this->userRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->with('new@signalist.app')
            ->willReturn(null);

        $this->passwordHasher
            ->expects($this->once())
            ->method('hashPassword')
            ->willReturn('hashed_password');

        $this->userRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(User::class));

        $command = new RegisterCommand(
            email: 'new@signalist.app',
            password: 'password123',
        );

        $result = ($this->handler)($command);

        $this->assertTrue(Uuid::isValid($result));
    }

    public function testInvokeWithExistingEmailThrowsEmailAlreadyExistsException(): void
    {
        $existingUser = $this->createMock(User::class);

        $this->userRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->with('admin@signalist.app')
            ->willReturn($existingUser);

        $this->expectException(EmailAlreadyExistsException::class);

        ($this->handler)(new RegisterCommand(
            email: 'admin@signalist.app',
            password: 'password123',
        ));
    }
}
