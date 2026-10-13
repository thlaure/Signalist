<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Sync;

use App\Domain\Sync\Service\SyncConflictResolver;
use PHPUnit\Framework\TestCase;

final class SyncConflictResolverTest extends TestCase
{
    public function testLocalWinsConcurrentEditsWhileRemoteOnlyChangesImport(): void
    {
        $resolver = new SyncConflictResolver();
        $this->assertSame('local', $resolver->direction('old-local', 'old-remote', 'new-local', 'new-remote'));
        $this->assertSame('remote', $resolver->direction('old-local', 'old-remote', 'old-local', 'new-remote'));
        $this->assertSame('local', $resolver->direction('old-local', 'old-remote', 'new-local', 'old-remote'));
        $this->assertSame('none', $resolver->direction('same-local', 'same-remote', 'same-local', 'same-remote'));
    }
}
