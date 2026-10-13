<?php

declare(strict_types=1);

namespace App\Domain\Social\Handler;

use App\Domain\Connection\Handler\ConnectionHandler;
use App\Domain\Social\DTO\CrosspostInput;
use App\Domain\Social\Message\CrosspostMessage;
use App\Domain\Social\Port\CrosspostAdapterInterface;
use App\Domain\Social\Service\PlatformFormatter;
use App\Entity\Crosspost;
use App\Entity\User;

use const DATE_ATOM;

use Doctrine\ORM\EntityManagerInterface;

use function in_array;

use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Uid\Uuid;

final readonly class CrosspostHandler
{
    public function __construct(private EntityManagerInterface $entityManager, private ConnectionHandler $connections, private PlatformFormatter $formatter, private CrosspostAdapterInterface $adapter, private MessageBusInterface $bus)
    {
    }

    public function create(User $user, CrosspostInput $input): Crosspost
    {
        if (!$input->confirmed || $user->isDeleted() || $user->isProcessingRestricted()) {
            throw new AccessDeniedException('Explicit publication approval is required.');
        }
        $account = $this->connections->account($user, $input->accountId);

        if (!in_array($account->getProvider(), ['linkedin', 'x', 'bluesky'], true) || 'connected' !== $account->getStatus() || null === $account->getRemoteId()) {
            throw new ConflictHttpException('Connect a supported social account first.');
        }

        try {
            $posts = $this->formatter->format($account->getProvider(), $input->text);
        } catch (InvalidArgumentException $error) {
            throw new BadRequestHttpException($error->getMessage());
        }
        $post = new Crosspost($account, $account->getRemoteId(), $posts, $this->adapter->recordKey($account->getProvider()));
        $this->entityManager->persist($post);
        $this->entityManager->flush();
        $this->bus->dispatch(new CrosspostMessage($post->getId()->toRfc4122()));

        return $post;
    }

    public function get(User $user, string $id): Crosspost
    {
        $post = Uuid::isValid($id) ? $this->entityManager->find(Crosspost::class, Uuid::fromString($id)) : null;

        if (!$post instanceof Crosspost || !$post->getAccount()->getOwner()->getId()->equals($user->getId())) {
            throw new NotFoundHttpException();
        }

        return $post;
    }

    /** @return list<Crosspost> */
    public function list(User $user): array
    {
        /** @var list<Crosspost> $posts */
        $posts = $this->entityManager->createQuery('SELECT p FROM App\\Entity\\Crosspost p JOIN p.account a WHERE a.owner = :owner ORDER BY p.approvedAt DESC')->setParameter('owner', $user)->setMaxResults(100)->getResult();

        return $posts;
    }

    /** @return array<string, mixed> */
    public function output(Crosspost $post): array
    {
        return ['id' => $post->getId()->toRfc4122(), 'platform' => $post->getAccount()->getProvider(), 'status' => $post->getStatus(), 'posts' => $post->getPosts(), 'publishedIds' => $post->getPublishedIds(), 'approvedAt' => $post->getApprovedAt()->format(DATE_ATOM)];
    }
}
