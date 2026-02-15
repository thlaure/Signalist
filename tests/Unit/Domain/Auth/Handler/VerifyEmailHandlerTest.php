<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Auth\Handler;

use App\Domain\Auth\Command\VerifyEmailCommand;
use App\Domain\Auth\Exception\InvalidVerificationTokenException;
use App\Domain\Auth\Handler\VerifyEmailHandler;
use App\Domain\Auth\Port\EmailVerificationTokenGeneratorInterface;
use App\Domain\Auth\Port\UserRepositoryInterface;
use App\Entity\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class VerifyEmailHandlerTest extends TestCase
{
    private EmailVerificationTokenGeneratorInterface&MockObject $tokenGenerator;

    private UserRepositoryInterface&MockObject $userRepository;

    private VerifyEmailHandler $handler;

    protected function setUp(): void
    {
        $this->tokenGenerator = $this->createMock(EmailVerificationTokenGeneratorInterface::class);
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);

        $this->handler = new VerifyEmailHandler(
            $this->tokenGenerator,
            $this->userRepository,
        );
    }

    public function testInvokeWithValidSignatureVerifiesUser(): void
    {
        $userId = Uuid::v7()->toRfc4122();
        $email = 'test@signalist.app';

        $user = $this->createMock(User::class);
        $user->method('getEmail')->willReturn($email);
        $user->method('isEmailVerified')->willReturn(false);

        $this->tokenGenerator
            ->expects($this->once())
            ->method('validateSignedUrl')
            ->with($userId, $email, 9999999999, 'valid-sig')
            ->willReturn(true);

        $this->userRepository
            ->expects($this->once())
            ->method('find')
            ->with($userId)
            ->willReturn($user);

        $user->expects($this->once())->method('setEmailVerifiedAt');

        $this->userRepository
            ->expects($this->once())
            ->method('save')
            ->with($user);

        ($this->handler)(new VerifyEmailCommand(
            userId: $userId,
            email: $email,
            expiresAt: 9999999999,
            signature: 'valid-sig',
        ));
    }

    public function testInvokeWithInvalidSignatureThrowsException(): void
    {
        $this->tokenGenerator
            ->method('validateSignedUrl')
            ->willReturn(false);

        $this->expectException(InvalidVerificationTokenException::class);

        ($this->handler)(new VerifyEmailCommand(
            userId: Uuid::v7()->toRfc4122(),
            email: 'test@signalist.app',
            expiresAt: 9999999999,
            signature: 'bad-sig',
        ));
    }

    public function testInvokeWithNonExistentUserThrowsException(): void
    {
        $this->tokenGenerator
            ->method('validateSignedUrl')
            ->willReturn(true);

        $this->userRepository
            ->method('find')
            ->willReturn(null);

        $this->expectException(InvalidVerificationTokenException::class);

        ($this->handler)(new VerifyEmailCommand(
            userId: Uuid::v7()->toRfc4122(),
            email: 'test@signalist.app',
            expiresAt: 9999999999,
            signature: 'valid-sig',
        ));
    }

    public function testInvokeWithEmailMismatchThrowsException(): void
    {
        $userId = Uuid::v7()->toRfc4122();

        $user = $this->createMock(User::class);
        $user->method('getEmail')->willReturn('other@signalist.app');

        $this->tokenGenerator
            ->method('validateSignedUrl')
            ->willReturn(true);

        $this->userRepository
            ->method('find')
            ->with($userId)
            ->willReturn($user);

        $this->expectException(InvalidVerificationTokenException::class);

        ($this->handler)(new VerifyEmailCommand(
            userId: $userId,
            email: 'test@signalist.app',
            expiresAt: 9999999999,
            signature: 'valid-sig',
        ));
    }

    public function testInvokeWithAlreadyVerifiedUserIsIdempotent(): void
    {
        $userId = Uuid::v7()->toRfc4122();
        $email = 'test@signalist.app';

        $user = $this->createMock(User::class);
        $user->method('getEmail')->willReturn($email);
        $user->method('isEmailVerified')->willReturn(true);

        $this->tokenGenerator
            ->method('validateSignedUrl')
            ->willReturn(true);

        $this->userRepository
            ->method('find')
            ->willReturn($user);

        $user->expects($this->never())->method('setEmailVerifiedAt');
        $this->userRepository->expects($this->never())->method('save');

        ($this->handler)(new VerifyEmailCommand(
            userId: $userId,
            email: $email,
            expiresAt: 9999999999,
            signature: 'valid-sig',
        ));
    }
}
