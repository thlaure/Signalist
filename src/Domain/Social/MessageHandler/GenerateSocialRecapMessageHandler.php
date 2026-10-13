<?php

declare(strict_types=1);

namespace App\Domain\Social\MessageHandler;

use App\Domain\Billing\Service\UsageBudget;
use App\Domain\Privacy\Service\ConsentPolicy;
use App\Domain\Social\Message\GenerateSocialRecapMessage;
use App\Domain\Social\Service\SocialRecapContentBuilder;
use App\Entity\Article;
use App\Entity\SocialRecap;
use App\Infrastructure\Async\JobLock;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Uid\Uuid;

#[AsMessageHandler]
final readonly class GenerateSocialRecapMessageHandler
{
    public function __construct(private EntityManagerInterface $entityManager, private SocialRecapContentBuilder $builder, private ConsentPolicy $consent, private UsageBudget $budget, private ?JobLock $jobLock = null)
    {
    }

    public function __invoke(GenerateSocialRecapMessage $message): void
    {
        if (!$this->jobLock instanceof JobLock) {
            $this->process($message);

            return;
        }
        $this->jobLock->run('recap-'.$message->recapId, fn () => $this->process($message));
    }

    private function process(GenerateSocialRecapMessage $message): void
    {
        $recap = $this->entityManager->find(SocialRecap::class, Uuid::fromString($message->recapId));

        if ($recap instanceof SocialRecap) {
            $this->entityManager->refresh($recap);
        }

        if (!$recap instanceof SocialRecap || 'pending' !== $recap->getStatus()) {
            return;
        }
        $owner = $recap->getOwner();

        if ($owner->isDeleted() || $owner->isProcessingRestricted() || ($recap->usesAi() && !$this->consent->allows($owner, 'external_ai'))) {
            $recap->fail();
            $this->entityManager->flush();

            return;
        }
        /** @var list<Article> $articles */
        $articles = $this->entityManager->createQueryBuilder()->select('a')->from(Article::class, 'a')->join('a.bookmark', 'b')->join('a.feed', 'f')
            ->where('f.owner = :owner')->andWhere('f.organization IS NULL')->andWhere('b.createdAt >= :start AND b.createdAt < :end')->setParameter('owner', $owner)->setParameter('start', $recap->getPeriodStart())->setParameter('end', $recap->getPeriodEnd())->orderBy('b.createdAt', 'DESC')->setMaxResults(50)->getQuery()->getResult();

        try {
            if ($recap->usesAi()) {
                $this->budget->aiCalls($owner, 1);
            }
            $posts = $this->builder->build($recap, $articles);
            $this->entityManager->refresh($owner);

            if ($owner->isDeleted() || $owner->isProcessingRestricted() || ($recap->usesAi() && !$this->consent->allows($owner, 'external_ai'))) {
                $recap->fail();
            } else {
                $recap->draft($posts);
            }
        } catch (InvalidArgumentException) {
            $recap->fail();
        }
        $this->entityManager->flush();
    }
}
