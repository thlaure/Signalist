<?php

declare(strict_types=1);

namespace App\Domain\Newsletter\Handler;

use App\Domain\Newsletter\DTO\NewsletterInput;
use App\Domain\Newsletter\Message\GenerateNewsletterMessage;
use App\Domain\Privacy\Exception\ConsentRequiredException;
use App\Domain\Privacy\Exception\ProcessingRestrictedException;
use App\Domain\Privacy\Service\ConsentPolicy;
use App\Entity\Newsletter;
use App\Entity\NewsletterRun;
use App\Entity\User;

use const DATE_ATOM;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;

final readonly class NewsletterHandler
{
    public function __construct(private EntityManagerInterface $entityManager, private MessageBusInterface $bus, private ConsentPolicy $consent)
    {
    }

    public function get(User $user, string $id): Newsletter
    {
        $newsletter = Uuid::isValid($id) ? $this->entityManager->find(Newsletter::class, Uuid::fromString($id)) : null;

        if (!$newsletter instanceof Newsletter || !$newsletter->getOwner()->getId()->equals($user->getId())) {
            throw new NotFoundHttpException();
        }

        return $newsletter;
    }

    /** @return list<Newsletter> */
    public function list(User $user): array
    {
        return $this->entityManager->getRepository(Newsletter::class)->findBy(['owner' => $user], ['createdAt' => 'DESC'], 100);
    }

    public function save(User $user, NewsletterInput $input, ?string $id = null): Newsletter
    {
        if ($user->isProcessingRestricted()) {
            throw new ProcessingRestrictedException();
        }
        $newsletter = null === $id ? new Newsletter($user) : $this->get($user, $id);
        $newsletter->configure(trim($input->title), $input->readingMinutes, $input->intervalHours);
        $this->entityManager->persist($newsletter);
        $this->entityManager->flush();

        return $newsletter;
    }

    public function delete(User $user, string $id): void
    {
        $this->entityManager->remove($this->get($user, $id));
        $this->entityManager->flush();
    }

    public function generate(User $user, string $id, bool $deliver = false): NewsletterRun
    {
        $newsletter = $this->get($user, $id);

        if ($user->isProcessingRestricted()) {
            throw new ProcessingRestrictedException();
        }

        if ($deliver && !$this->consent->allows($user, 'newsletter')) {
            throw new ConsentRequiredException('newsletter');
        }
        $run = new NewsletterRun($newsletter, 'manual-'.Uuid::v7()->toRfc4122(), $deliver);
        $this->entityManager->persist($run);
        $this->entityManager->flush();
        $this->bus->dispatch(new GenerateNewsletterMessage($run->getId()->toRfc4122()));

        return $run;
    }

    /** @return array<string, mixed> */
    public function output(Newsletter $newsletter): array
    {
        return ['id' => $newsletter->getId()->toRfc4122(), 'title' => $newsletter->getTitle(), 'readingMinutes' => $newsletter->getReadingMinutes(), 'intervalHours' => $newsletter->getIntervalHours(), 'nextRunAt' => $newsletter->getNextRunAt()?->format(DATE_ATOM), 'html' => $newsletter->getHtml(), 'generatedAt' => $newsletter->getGeneratedAt()?->format(DATE_ATOM), 'lastRunStatus' => $this->entityManager->getRepository(NewsletterRun::class)->findOneBy(['newsletter' => $newsletter], ['createdAt' => 'DESC'])?->getStatus()];
    }
}
