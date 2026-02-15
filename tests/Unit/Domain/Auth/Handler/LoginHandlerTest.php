<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Auth\Handler;

use App\Domain\Auth\Command\LoginCommand;
use App\Domain\Auth\Exception\EmailNotVerifiedException;
use App\Domain\Auth\Exception\InvalidCredentialsException;
use App\Domain\Auth\Handler\LoginHandler;
use App\Domain\Auth\Port\UserRepositoryInterface;
use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class LoginHandlerTest extends TestCase
{
    private UserRepositoryInterface&MockObject $userRepository;

    private UserPasswordHasherInterface&MockObject $passwordHasher;

    private JWTTokenManagerInterface&MockObject $jwtManager;

    private LoginHandler $handler;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->jwtManager = $this->createMock(JWTTokenManagerInterface::class);

        $this->handler = new LoginHandler(
            $this->userRepository,
            $this->passwordHasher,
            $this->jwtManager,
        );
    }

    public function testInvokeWithValidCredentialsReturnsToken(): void
    {
        $user = $this->createMock(User::class);
        $user->method('isDeleted')->willReturn(false);
        $user->method('isEmailVerified')->willReturn(true);

        $this->userRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->with('admin@signalist.app')
            ->willReturn($user);

        $this->passwordHasher
            ->expects($this->once())
            ->method('isPasswordValid')
            ->with($user, 'password123')
            ->willReturn(true);

        $this->jwtManager
            ->expects($this->once())
            ->method('create')
            ->with($user)
            ->willReturn('eyJ.test.token');

        $command = new LoginCommand(
            email: 'admin@signalist.app',
            password: 'password123',
        );

        $result = ($this->handler)($command);

        $this->assertSame('eyJ.test.token', $result['token']);
        $this->assertSame(3600, $result['expiresIn']);
    }

    public function testInvokeWithNonExistentUserThrowsInvalidCredentialsException(): void
    {
        $this->userRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->with('unknown@signalist.app')
            ->willReturn(null);

        $this->expectException(InvalidCredentialsException::class);

        ($this->handler)(new LoginCommand(
            email: 'unknown@signalist.app',
            password: 'password123',
        ));
    }

    public function testInvokeWithWrongPasswordThrowsInvalidCredentialsException(): void
    {
        $user = $this->createMock(User::class);
        $user->method('isDeleted')->willReturn(false);

        $this->userRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->with('admin@signalist.app')
            ->willReturn($user);

        $this->passwordHasher
            ->expects($this->once())
            ->method('isPasswordValid')
            ->with($user, 'wrongpassword')
            ->willReturn(false);

        $this->expectException(InvalidCredentialsException::class);

        ($this->handler)(new LoginCommand(
            email: 'admin@signalist.app',
            password: 'wrongpassword',
        ));
    }

    public function testInvokeWithDeletedUserThrowsInvalidCredentialsException(): void
    {
        $user = $this->createMock(User::class);
        $user->method('isDeleted')->willReturn(true);

        $this->userRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->with('deleted@signalist.app')
            ->willReturn($user);

        $this->expectException(InvalidCredentialsException::class);

        ($this->handler)(new LoginCommand(
            email: 'deleted@signalist.app',
            password: 'password123',
        ));
    }

    public function testInvokeWithUnverifiedEmailThrowsEmailNotVerifiedException(): void
    {
        $user = $this->createMock(User::class);
        $user->method('isDeleted')->willReturn(false);
        $user->method('isEmailVerified')->willReturn(false);

        $this->userRepository
            ->expects($this->once())
            ->method('findByEmail')
            ->with('unverified@signalist.app')
            ->willReturn($user);

        $this->passwordHasher
            ->expects($this->once())
            ->method('isPasswordValid')
            ->with($user, 'password123')
            ->willReturn(true);

        $this->expectException(EmailNotVerifiedException::class);

        ($this->handler)(new LoginCommand(
            email: 'unverified@signalist.app',
            password: 'password123',
        ));
    }
}
