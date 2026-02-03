<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Feed;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class FeedFixture extends Fixture implements OrderedFixtureInterface
{
    private const array FEEDS = [
        'smashing' => [
            'title' => 'Smashing Magazine',
            'url' => 'https://www.smashingmagazine.com/feed/',
            'status' => Feed::STATUS_ACTIVE,
            'category' => 'category-tech',
            'lastFetchedAt' => '-2 hours',
        ],
        'css-tricks' => [
            'title' => 'CSS-Tricks',
            'url' => 'https://css-tricks.com/feed/',
            'status' => Feed::STATUS_ACTIVE,
            'category' => 'category-tech',
            'lastFetchedAt' => '-5 hours',
        ],
        'techcrunch' => [
            'title' => 'TechCrunch',
            'url' => 'https://techcrunch.com/feed/',
            'status' => Feed::STATUS_ACTIVE,
            'category' => 'category-business',
            'lastFetchedAt' => '-1 hour',
        ],
        'entrepreneur' => [
            'title' => 'Entrepreneur',
            'url' => 'https://www.entrepreneur.com/rss/',
            'status' => Feed::STATUS_PAUSED,
            'category' => 'category-business',
            'lastFetchedAt' => '-3 days',
        ],
        'ux-planet' => [
            'title' => 'UX Planet',
            'url' => 'https://uxplanet.org/feed',
            'status' => Feed::STATUS_ACTIVE,
            'category' => 'category-design',
            'lastFetchedAt' => '-4 hours',
        ],
        'mit-review' => [
            'title' => 'MIT Technology Review',
            'url' => 'https://www.technologyreview.com/rss/',
            'status' => Feed::STATUS_ERROR,
            'category' => 'category-science',
            'lastFetchedAt' => '-1 day',
            'lastError' => 'Connection timeout after 30s',
        ],
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::FEEDS as $key => $data) {
            $feed = new Feed();
            $feed->setTitle($data['title']);
            $feed->setUrl($data['url']);
            $feed->setStatus($data['status']);
            $feed->setCategory($this->getReference($data['category'], \App\Entity\Category::class));
            $feed->setLastFetchedAt(new DateTimeImmutable($data['lastFetchedAt']));

            if (isset($data['lastError'])) {
                $feed->setLastError($data['lastError']);
            }

            $manager->persist($feed);
            $this->addReference("feed-{$key}", $feed);
        }

        $manager->flush();
    }

    public function getOrder(): int
    {
        return 2;
    }
}
