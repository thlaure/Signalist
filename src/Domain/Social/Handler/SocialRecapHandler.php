<?php

declare(strict_types=1);

namespace App\Domain\Social\Handler;

use App\Domain\Privacy\Exception\ConsentRequiredException;
use App\Domain\Privacy\Service\ConsentPolicy;
use App\Domain\Social\DTO\RecapInput;
use App\Domain\Social\Message\GenerateSocialRecapMessage;
use App\Domain\Social\Port\RecapIntelligenceInterface;
use App\Domain\Social\Service\PlatformFormatter;
use App\Entity\SocialRecap;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Uid\Uuid;

final readonly class SocialRecapHandler
{
    public function __construct(private EntityManagerInterface $entityManager, private MessageBusInterface $bus, private ConsentPolicy $consent, private RecapIntelligenceInterface $intelligence, private PlatformFormatter $formatter)
    {
    }

    public function create(User $user, RecapInput $input): SocialRecap
    {
        if ($user->isDeleted() || $user->isProcessingRestricted()) {
            throw new AccessDeniedException();
        }

        if ($input->useAi && !$this->consent->allows($user, 'external_ai')) {
            throw new ConsentRequiredException('external_ai');
        }

        if ($input->useAi && !$this->intelligence->configured()) {
            throw new ServiceUnavailableHttpException(null, 'AI provider setup is pending.');
        }
        $start = new DateTimeImmutable($input->start.'T00:00:00+00:00');
        $end = new DateTimeImmutable($input->end.'T00:00:00+00:00')->modify('+1 day');

        if ($start >= $end || $end > new DateTimeImmutable('tomorrow UTC') || $end->diff($start)->days > 366) {
            throw new BadRequestHttpException('Choose a past period of at most one year.');
        }
        $recap = new SocialRecap($user, $input->platform, $start, $end, $input->useAi);
        $this->entityManager->persist($recap);
        $this->entityManager->flush();
        $this->bus->dispatch(new GenerateSocialRecapMessage($recap->getId()->toRfc4122()));

        return $recap;
    }

    public function get(User $user, string $id): SocialRecap
    {
        $recap = Uuid::isValid($id) ? $this->entityManager->find(SocialRecap::class, Uuid::fromString($id)) : null;

        if (!$recap instanceof SocialRecap || !$recap->getOwner()->getId()->equals($user->getId())) {
            throw new NotFoundHttpException();
        }

        return $recap;
    }

    public function edit(User $user, string $id, string $text): SocialRecap
    {
        if ($user->isProcessingRestricted()) {
            throw new AccessDeniedException();
        }
        $recap = $this->get($user, $id);

        try {
            $recap->draft($this->formatter->format($recap->getPlatform(), $text));
        } catch (InvalidArgumentException $error) {
            throw new BadRequestHttpException($error->getMessage());
        }
        $this->entityManager->flush();

        return $recap;
    }

    public function delete(User $user, string $id): void
    {
        $this->entityManager->remove($this->get($user, $id));
        $this->entityManager->flush();
    }

    /** @return list<SocialRecap> */
    public function list(User $user): array
    {
        return $this->entityManager->getRepository(SocialRecap::class)->findBy(['owner' => $user], ['createdAt' => 'DESC'], 100);
    }

    /** @return array<string, mixed> */
    public function output(SocialRecap $recap): array
    {
        return ['id' => $recap->getId()->toRfc4122(), 'platform' => $recap->getPlatform(), 'start' => $recap->getPeriodStart()->format('Y-m-d'), 'end' => $recap->getPeriodEnd()->modify('-1 day')->format('Y-m-d'), 'status' => $recap->getStatus(), 'posts' => $recap->getPosts(), 'useAi' => $recap->usesAi()];
    }
}
