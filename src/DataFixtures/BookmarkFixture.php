<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Bookmark;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class BookmarkFixture extends Fixture implements OrderedFixtureInterface
{
    private const array BOOKMARKS = [
        [
            'article' => 'article-smashing-1',
            'notes' => 'Great reference for the new dashboard layout',
        ],
        [
            'article' => 'article-css-tricks-1',
            'notes' => null,
        ],
        [
            'article' => 'article-techcrunch-1',
            'notes' => 'Share with the team during standup',
        ],
        [
            'article' => 'article-ux-planet-1',
            'notes' => 'Must read before the accessibility audit',
        ],
        [
            'article' => 'article-openai-1',
            'notes' => 'Evaluate GPT-5 for summarization pipeline',
        ],
        [
            'article' => 'article-cal-1',
            'notes' => null,
        ],
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::BOOKMARKS as $index => $data) {
            $bookmark = new Bookmark();
            $bookmark->setArticle($this->getReference($data['article'], \App\Entity\Article::class));
            $bookmark->setNotes($data['notes']);

            $manager->persist($bookmark);
            $this->addReference("bookmark-{$index}", $bookmark);
        }

        $manager->flush();
    }

    public function getOrder(): int
    {
        return 4;
    }
}
