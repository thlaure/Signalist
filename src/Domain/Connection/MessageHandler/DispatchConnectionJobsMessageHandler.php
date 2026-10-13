<?php

declare(strict_types=1);

namespace App\Domain\Connection\MessageHandler;

use App\Domain\Connection\Message\DispatchConnectionJobsMessage;
use App\Domain\Connection\Message\ExchangeOAuthCodeMessage;
use App\Domain\Connection\Message\PrepareBlueskyAuthorizationMessage;
use App\Domain\Connection\Message\RefreshAccountMessage;
use App\Domain\Search\Message\RunArticleSearchMessage;
use App\Domain\Social\Message\CrosspostMessage;
use App\Domain\Social\Message\GenerateSocialRecapMessage;
use App\Domain\Spotlight\Message\AnswerArticleQuestionMessage;
use App\Domain\Sync\Message\SyncRaindropMessage;
use App\Entity\ArticleSearch;
use App\Entity\BookmarkSyncRun;
use App\Entity\Crosspost;
use App\Entity\ExternalAccount;
use App\Entity\OAuthAttempt;
use App\Entity\SocialRecap;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final readonly class DispatchConnectionJobsMessageHandler
{
    public function __construct(private EntityManagerInterface $entityManager, private MessageBusInterface $bus)
    {
    }

    public function __invoke(DispatchConnectionJobsMessage $message): void
    {
        foreach ($this->entityManager->getRepository(OAuthAttempt::class)->findBy(['status' => ['ready', 'preparing']], [], 100) as $attempt) {
            if ($attempt->getExpiresAt() < new DateTimeImmutable()) {
                $attempt->finish(false);
                continue;
            }
            $id = $attempt->getId()->toRfc4122();
            $this->bus->dispatch('ready' === $attempt->getStatus() ? new ExchangeOAuthCodeMessage($id) : new PrepareBlueskyAuthorizationMessage($id));
        }
        foreach ($this->entityManager->getRepository(BookmarkSyncRun::class)->findBy(['status' => 'pending'], [], 100) as $run) {
            $this->bus->dispatch(new SyncRaindropMessage($run->getId()->toRfc4122()));
        }
        foreach ($this->entityManager->getRepository(ExternalAccount::class)->findBy(['status' => 'connected'], [], 100) as $account) {
            if ('bluesky' !== $account->getProvider() && $account->getExpiresAt() < new DateTimeImmutable('+5 minutes')) {
                $this->bus->dispatch(new RefreshAccountMessage($account->getId()->toRfc4122()));
            }
        }
        foreach ($this->entityManager->getRepository(SocialRecap::class)->findBy(['status' => 'pending'], [], 100) as $recap) {
            $this->bus->dispatch(new GenerateSocialRecapMessage($recap->getId()->toRfc4122()));
        }
        foreach ($this->entityManager->getRepository(Crosspost::class)->findBy(['status' => 'pending'], [], 100) as $post) {
            $this->bus->dispatch(new CrosspostMessage($post->getId()->toRfc4122()));
        }
        foreach ($this->entityManager->getRepository(ArticleSearch::class)->findBy(['status' => 'pending'], [], 100) as $search) {
            $id = $search->getId()->toRfc4122();
            $this->bus->dispatch('question' === $search->getMode() ? new AnswerArticleQuestionMessage($id) : new RunArticleSearchMessage($id));
        }
        $this->entityManager->flush();
    }
}
