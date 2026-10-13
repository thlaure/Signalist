<?php

declare(strict_types=1);

namespace App\Domain\Tag\Service;

use App\Entity\Tag;
use App\Entity\User;

use function array_slice;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;

final readonly class TagCatalog
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /**
     * Caller holds an ORM transaction.
     *
     * @param list<string> $names
     *
     * @return list<string>
     */
    public function register(User $user, array $names): array
    {
        $this->entityManager->refresh($user, LockMode::PESSIMISTIC_WRITE);
        $result = [];
        foreach (array_slice($names, 0, 20) as $name) {
            $name = mb_substr(trim($name), 0, 60);
            $key = mb_strtolower($name);

            if ('' === $name || isset($result[$key])) {
                continue;
            }
            $tag = $this->entityManager->getRepository(Tag::class)->findOneBy(['owner' => $user, 'nameKey' => $key]);

            if (!$tag instanceof Tag) {
                $tag = new Tag($user, $name);
                $this->entityManager->persist($tag);
            }
            $result[$key] = $tag->getName();
        }

        return array_values($result);
    }
}
