<?php

declare(strict_types=1);

namespace App\Domain\Digest\Handler;

use App\Entity\User;
use App\Entity\WeeklyDigest;

use const DATE_ATOM;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Uid\Uuid;

final readonly class DigestHandler
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    /** @return list<WeeklyDigest> */
    public function list(User $user): array
    {
        return $this->em->getRepository(WeeklyDigest::class)->findBy(['owner' => $user], ['period' => 'DESC'], 100);
    }

    public function get(User $user, string $id): WeeklyDigest
    {
        $digest = Uuid::isValid($id) ? $this->em->find(WeeklyDigest::class, Uuid::fromString($id)) : null;

        if (!$digest instanceof WeeklyDigest || !$digest->getOwner()->getId()->equals($user->getId())) {
            throw new NotFoundHttpException();
        }

        return $digest;
    }

    /** @return array<string, mixed> */
    public static function output(WeeklyDigest $digest): array
    {
        return ['id' => $digest->getId()->toRfc4122(), 'period' => $digest->getPeriod(), 'status' => $digest->getStatus(), 'items' => $digest->getItems(), 'sentAt' => $digest->getSentAt()?->format(DATE_ATOM)];
    }
}
