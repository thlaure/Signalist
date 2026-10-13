<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Auth;

use App\Entity\User;
use App\Infrastructure\Auth\ActiveUserChecker;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\DisabledException;

final class ActiveUserCheckerTest extends TestCase
{
    public function testExistingTokenCannotAuthenticateDeletedAccount(): void
    {
        $user = new User()->setEmailVerifiedAt(new DateTimeImmutable())->setDeletedAt(new DateTimeImmutable());
        $this->expectException(DisabledException::class);
        new ActiveUserChecker()->checkPreAuth($user);
    }

    public function testVerifiedRestrictedUserCanStillAccessPrivacyControls(): void
    {
        $user = new User()->setEmailVerifiedAt(new DateTimeImmutable())->setProcessingRestricted(true);
        new ActiveUserChecker()->checkPreAuth($user);
        $this->assertTrue($user->isProcessingRestricted());
    }
}
