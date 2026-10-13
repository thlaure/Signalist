<?php

declare(strict_types=1);

namespace App\Domain\Waitlist\Handler;

use App\Domain\Waitlist\Message\SendWaitlistConfirmationMessage;
use App\Entity\WaitlistEntry;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class WaitlistHandler
{
    public function __construct(private EntityManagerInterface $entityManager, private MessageBusInterface $bus)
    {
    }

    public function join(string $email): void
    {
        $email = mb_strtolower(trim($email));

        if (null !== $this->entityManager->getRepository(WaitlistEntry::class)->findOneBy(['email' => $email])) {
            return;
        }
        $entry = new WaitlistEntry($email);

        try {
            $this->entityManager->persist($entry);
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException) {
            return;
        }
        $this->bus->dispatch(new SendWaitlistConfirmationMessage($entry->getId()->toRfc4122()));
    }
}
