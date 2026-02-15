<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Auth\Handler;

use App\Domain\Auth\Command\ResendVerificationCommand;
use App\Domain\Auth\Handler\ResendVerificationHandler;
use App\Domain\Auth\Message\SendVerificationEmailMessage;
use App\Domain\Auth\Port\UserRepositoryInterface;
use App\Entity\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;

final class ResendVerificationHandlerTest extends TestCase
{
    private UserRepositoryInterface&MockObject $userRepository;

    private MessageBusInterface&MockObject $messageBus;

    private ResendVerificationHandler $handler;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->messageBus = $this->createMock(MessageBusInterface::class);

        $this->handler = new ResendVerificationHandler(
            $this->userRepository,
            $this->messageBus,
        );
    }

    public function testInvokeWithUnverifiedUserDispatchesMessage(): void
    {
        $userId = Uuid::v7();
        $email = 'test@signalist.app';

        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn($userId);
        $user->method('getEmail')->willReturn($email);
        $user->method('isDeleted')->willReturn(false);
        $user->method('isEmailVerified')->willReturn(false);

        $this->userRepository
            ->method('findByEmail')
            ->with($email)
            ->willReturn($user);

        $this->messageBus
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(SendVerificationEmailMessage::class))
            ->willReturn(new Envelope(new stdClass()));

        ($this->handler)(new ResendVerificationCommand(email: $email));
    }

    public function testInvokeWithNonExistentUserDoesNotDispatch(): void
    {
        $this->userRepository
            ->method('findByEmail')
            ->willReturn(null);

        $this->messageBus
            ->expects($this->never())
            ->method('dispatch');

        ($this->handler)(new ResendVerificationCommand(email: 'unknown@signalist.app'));
    }

    public function testInvokeWithVerifiedUserDoesNotDispatch(): void
    {
        $user = $this->createMock(User::class);
        $user->method('isDeleted')->willReturn(false);
        $user->method('isEmailVerified')->willReturn(true);

        $this->userRepository
            ->method('findByEmail')
            ->willReturn($user);

        $this->messageBus
            ->expects($this->never())
            ->method('dispatch');

        ($this->handler)(new ResendVerificationCommand(email: 'verified@signalist.app'));
    }

    public function testInvokeWithDeletedUserDoesNotDispatch(): void
    {
        $user = $this->createMock(User::class);
        $user->method('isDeleted')->willReturn(true);

        $this->userRepository
            ->method('findByEmail')
            ->willReturn($user);

        $this->messageBus
            ->expects($this->never())
            ->method('dispatch');

        ($this->handler)(new ResendVerificationCommand(email: 'deleted@signalist.app'));
    }
}
