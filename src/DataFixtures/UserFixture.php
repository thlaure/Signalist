<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserFixture extends Fixture implements OrderedFixtureInterface
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $admin = new User();
        $admin->setEmail('admin@signalist.app');
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'password'));
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setEmailVerifiedAt(new DateTimeImmutable());
        $manager->persist($admin);
        $this->addReference('user-admin', $admin);

        $user = new User();
        $user->setEmail('user@signalist.app');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));
        $user->setEmailVerifiedAt(new DateTimeImmutable());
        $manager->persist($user);
        $this->addReference('user-default', $user);

        $manager->flush();
    }

    public function getOrder(): int
    {
        return 0;
    }
}
