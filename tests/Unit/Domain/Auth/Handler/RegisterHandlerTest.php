<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Auth\Handler;

use App\Domain\Auth\Command\RegisterCommand;
use App\Domain\Auth\Exception\EmailAlreadyExistsException;
use App\Domain\Auth\Handler\RegisterHandler;
use App\Domain\Auth\Message\SendVerificationEmailMessage;
use App\Domain\Auth\Port\UserRepositoryInterface;
use App\Entity\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Uuid;

final class RegisterHandlerTest extends TestCase
{
    private UserRepositoryInterface&MockObject $userRepository;

    private UserPasswordHasherInterface&MockObject $passwordHasher;

    private MessageBusInterface&MockObject $messageBus;

    private RegisterHandler $handler;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->messageBus = $this->createMock(MessageBusInterface::class);

        $this->handler = new RegisterHandler(
            $this->userRepository,
            $this->passwordHasher,
            $this->messageBus,
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

        $this->messageBus
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(SendVerificationEmailMessage::class))
            ->willReturn(new Envelope(new stdClass()));

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
