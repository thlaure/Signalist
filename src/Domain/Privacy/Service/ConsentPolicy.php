<?php

declare(strict_types=1);

namespace App\Domain\Privacy\Service;

use App\Entity\User;
use App\Entity\UserConsent;
use Doctrine\ORM\EntityManagerInterface;

final readonly class ConsentPolicy
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function allows(User $user, string $purpose): bool
    {
        if ($user->isDeleted() || $user->isProcessingRestricted()) {
            return false;
        }

        $consent = $this->entityManager->getRepository(UserConsent::class)
            ->findOneBy(['user' => $user, 'purpose' => $purpose], ['createdAt' => 'DESC', 'id' => 'DESC']);

        return $consent instanceof UserConsent && $consent->isGranted();
    }
}
