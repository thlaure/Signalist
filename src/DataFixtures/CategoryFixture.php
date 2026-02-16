<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class CategoryFixture extends Fixture implements OrderedFixtureInterface
{
    private const array CATEGORIES = [
        'tech' => [
            'name' => 'Tech & Development',
            'slug' => 'tech-development',
            'description' => 'Software engineering, web dev, and open source',
            'color' => '#3498db',
            'position' => 0,
        ],
        'business' => [
            'name' => 'Business & Startups',
            'slug' => 'business-startups',
            'description' => 'Entrepreneurship, funding, and market trends',
            'color' => '#e74c3c',
            'position' => 1,
        ],
        'design' => [
            'name' => 'Design & UX',
            'slug' => 'design-ux',
            'description' => 'UI/UX, product design, and visual identity',
            'color' => '#9b59b6',
            'position' => 2,
        ],
        'science' => [
            'name' => 'Science & Innovation',
            'slug' => 'science-innovation',
            'description' => 'Research, AI, and emerging technologies',
            'color' => '#2ecc71',
            'position' => 3,
        ],
        'ai' => [
            'name' => 'AI & Machine Learning',
            'slug' => 'ai-machine-learning',
            'description' => 'Artificial intelligence, deep learning, and LLMs',
            'color' => '#e67e22',
            'position' => 4,
        ],
        'productivity' => [
            'name' => 'Productivity',
            'slug' => 'productivity',
            'description' => 'Tools, workflows, and personal effectiveness',
            'color' => '#1abc9c',
            'position' => 5,
        ],
    ];

    public function load(ObjectManager $manager): void
    {
        /** @var User $admin */
        $admin = $this->getReference('user-admin', User::class);

        foreach (self::CATEGORIES as $key => $data) {
            $category = new Category();
            $category->setName($data['name']);
            $category->setSlug($data['slug']);
            $category->setDescription($data['description']);
            $category->setColor($data['color']);
            $category->setPosition($data['position']);
            $category->setOwner($admin);

            $manager->persist($category);
            $this->addReference("category-{$key}", $category);
        }

        $manager->flush();
    }

    public function getOrder(): int
    {
        return 1;
    }
}
