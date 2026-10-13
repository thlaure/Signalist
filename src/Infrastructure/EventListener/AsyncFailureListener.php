<?php

declare(strict_types=1);

namespace App\Infrastructure\EventListener;

use App\Domain\Billing\Message\CreateBillingLinkMessage;
use App\Domain\Connection\Message\ExchangeOAuthCodeMessage;
use App\Domain\Connection\Message\PrepareBlueskyAuthorizationMessage;
use App\Domain\Newsletter\Message\GenerateNewsletterMessage;
use App\Domain\Search\Message\RunArticleSearchMessage;
use App\Domain\Social\Message\CrosspostMessage;
use App\Domain\Social\Message\GenerateSocialRecapMessage;
use App\Domain\Spotlight\Message\AnswerArticleQuestionMessage;
use App\Domain\Sync\Message\SyncRaindropMessage;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;

/** Persist terminal status through DBAL even when an ORM transaction closed the entity manager. */
#[AsEventListener(event: WorkerMessageFailedEvent::class, priority: -200)]
final readonly class AsyncFailureListener
{
    public function __construct(private Connection $connection)
    {
    }

    public function __invoke(WorkerMessageFailedEvent $event): void
    {
        if ($event->willRetry()) {
            return;
        }
        $message = $event->getEnvelope()->getMessage();

        if ($message instanceof ExchangeOAuthCodeMessage || $message instanceof PrepareBlueskyAuthorizationMessage) {
            $this->connection->executeStatement("UPDATE oauth_attempt SET status = 'failed', encrypted_code = NULL, encrypted_verifier = NULL, authorization_url = NULL WHERE id = :id AND status IN ('ready', 'preparing')", ['id' => $message->attemptId]);
        } elseif ($message instanceof GenerateSocialRecapMessage) {
            $this->connection->executeStatement("UPDATE social_recap SET status = 'failed' WHERE id = :id AND status = 'pending'", ['id' => $message->recapId]);
        } elseif ($message instanceof CrosspostMessage) {
            $this->connection->executeStatement("UPDATE crosspost SET status = 'failed' WHERE id = :id AND status = 'pending'", ['id' => $message->crosspostId]);
        } elseif ($message instanceof SyncRaindropMessage) {
            $this->connection->executeStatement("UPDATE bookmark_sync_run SET status = 'failed' WHERE id = :id AND status = 'pending'", ['id' => $message->runId]);
        } elseif ($message instanceof CreateBillingLinkMessage) {
            $this->connection->executeStatement("UPDATE billing_link SET status = 'failed' WHERE id = :id AND status = 'pending'", ['id' => $message->linkId]);
        } elseif ($message instanceof RunArticleSearchMessage || $message instanceof AnswerArticleQuestionMessage) {
            $this->connection->executeStatement("UPDATE article_search SET status = 'failed' WHERE id = :id AND status = 'pending'", ['id' => $message->searchId]);
        } elseif ($message instanceof GenerateNewsletterMessage) {
            $this->connection->executeStatement("UPDATE newsletter_run SET status = CASE WHEN status = 'sending' THEN 'uncertain' ELSE 'failed' END WHERE id = :id AND status IN ('queued', 'generating', 'sending')", ['id' => $message->runId]);
        }
    }
}
