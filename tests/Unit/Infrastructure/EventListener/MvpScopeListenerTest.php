<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\EventListener;

use App\Domain\Digest\Message\GenerateWeeklyDigestMessage;
use App\Domain\Newsletter\Message\GenerateNewsletterMessage;
use App\Infrastructure\EventListener\MvpScopeListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;

final class MvpScopeListenerTest extends TestCase
{
    public function testOldQueuedNewslettersAreDisabledAndDigestsRemainEnabled(): void
    {
        $listener = new MvpScopeListener(true);
        $legacy = new WorkerMessageReceivedEvent(new Envelope(new GenerateNewsletterMessage('legacy')), 'async');
        $digest = new WorkerMessageReceivedEvent(new Envelope(new GenerateWeeklyDigestMessage('new')), 'async');
        $listener->received($legacy);
        $listener->received($digest);
        $this->assertFalse($legacy->shouldHandle());
        $this->assertTrue($digest->shouldHandle());
    }

    public function testMcpIsDisabledInMvpMode(): void
    {
        $event = new RequestEvent($this->createStub(HttpKernelInterface::class), Request::create('/mcp/'), HttpKernelInterface::MAIN_REQUEST);
        $this->expectException(NotFoundHttpException::class);
        new MvpScopeListener(true)->request($event);
    }
}
