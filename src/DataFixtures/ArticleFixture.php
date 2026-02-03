<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Article;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class ArticleFixture extends Fixture implements OrderedFixtureInterface
{
    private const ARTICLES = [
        'article-smashing-1' => [
            'feed' => 'feed-smashing',
            'guid' => 'https://www.smashingmagazine.com/2026/01/modern-css-grid/',
            'title' => 'A Complete Guide to Modern CSS Grid Layouts',
            'url' => 'https://www.smashingmagazine.com/2026/01/modern-css-grid/',
            'summary' => 'CSS Grid has evolved significantly. This guide covers subgrid, named areas, and dynamic layout patterns.',
            'content' => 'CSS Grid is one of the most powerful layout tools available. In this article, we explore subgrid support, named grid areas, and how to combine them for responsive layouts without media queries.',
            'author' => 'Rachel Andrew',
            'imageUrl' => 'https://cdn.smashingmagazine.com/articles/css-grid/header.jpg',
            'isRead' => true,
            'publishedAt' => '-3 days',
        ],
        'article-smashing-2' => [
            'feed' => 'feed-smashing',
            'guid' => 'https://www.smashingmagazine.com/2026/01/performance-web-apps/',
            'title' => 'Performance Optimization for Single-Page Apps in 2026',
            'url' => 'https://www.smashingmagazine.com/2026/01/performance-web-apps/',
            'summary' => 'Profiling, code splitting, and lazy loading strategies to cut LCP in half.',
            'content' => null,
            'author' => 'Addy Osmani',
            'imageUrl' => null,
            'isRead' => false,
            'publishedAt' => '-1 day',
        ],
        'article-css-tricks-1' => [
            'feed' => 'feed-css-tricks',
            'guid' => 'https://css-tricks.com/2026/01/container-queries/',
            'title' => 'Container Queries Are Now Everywhere — Here\'s How to Use Them',
            'url' => 'https://css-tricks.com/2026/01/container-queries/',
            'summary' => 'Container queries finally have full browser support. Time to use them in production.',
            'content' => 'Container queries allow you to style components based on the size of their parent container rather than the viewport. With full browser support, it\'s time to adopt them.',
            'author' => 'Chris Coyier',
            'imageUrl' => 'https://css-tricks.com/images/container-queries.png',
            'isRead' => true,
            'publishedAt' => '-2 days',
        ],
        'article-css-tricks-2' => [
            'feed' => 'feed-css-tricks',
            'guid' => 'https://css-tricks.com/2026/01/css-color-functions/',
            'title' => 'Color Functions in CSS: oklch, color-mix, and More',
            'url' => 'https://css-tricks.com/2026/01/css-color-functions/',
            'summary' => 'The new CSS color functions offer perceptually uniform color manipulation.',
            'content' => null,
            'author' => 'Chris Coyier',
            'imageUrl' => null,
            'isRead' => false,
            'publishedAt' => '-6 hours',
        ],
        'article-techcrunch-1' => [
            'feed' => 'feed-techcrunch',
            'guid' => 'https://techcrunch.com/2026/01/ai-startup-funding/',
            'title' => 'AI Startups Raised $42B in Q4 2025 — A Record Quarter',
            'url' => 'https://techcrunch.com/2026/01/ai-startup-funding/',
            'summary' => 'Venture capital poured into AI companies at an unprecedented rate last quarter.',
            'content' => 'The fourth quarter of 2025 saw AI-focused startups raise a combined $42 billion, shattering previous records and signaling sustained investor confidence in the sector.',
            'author' => 'Kate Clark',
            'imageUrl' => 'https://techcrunch.com/images/ai-funding-chart.png',
            'isRead' => false,
            'publishedAt' => '-8 hours',
        ],
        'article-techcrunch-2' => [
            'feed' => 'feed-techcrunch',
            'guid' => 'https://techcrunch.com/2026/01/open-source-ai-models/',
            'title' => 'Open-Source AI Models Are Catching Up to Closed-Source Giants',
            'url' => 'https://techcrunch.com/2026/01/open-source-ai-models/',
            'summary' => 'Llama 4 and Mistral Large show that open models are closing the gap fast.',
            'content' => null,
            'author' => 'Ron Harris',
            'imageUrl' => null,
            'isRead' => true,
            'publishedAt' => '-2 days',
        ],
        'article-entrepreneur-1' => [
            'feed' => 'feed-entrepreneur',
            'guid' => 'https://www.entrepreneur.com/article/100001/',
            'title' => '5 Bootstrapping Strategies That Actually Work in 2026',
            'url' => 'https://www.entrepreneur.com/article/100001/',
            'summary' => 'Forget VC money. These founders built profitable businesses on shoestring budgets.',
            'content' => 'Bootstrapping has a bad reputation for being impossible at scale. But these five founders prove that with the right strategies, you can build a sustainable business without outside funding.',
            'author' => 'Jason Fried',
            'imageUrl' => 'https://www.entrepreneur.com/images/bootstrapping.jpg',
            'isRead' => false,
            'publishedAt' => '-5 days',
        ],
        'article-ux-planet-1' => [
            'feed' => 'feed-ux-planet',
            'guid' => 'https://uxplanet.org/designing-for-neurodivergent-users-101/',
            'title' => 'Designing for Neurodivergent Users: An Accessibility Deep Dive',
            'url' => 'https://uxplanet.org/designing-for-neurodivergent-users-101/',
            'summary' => 'How to design interfaces that are truly inclusive for ADHD, autism, and dyslexia.',
            'content' => 'Accessibility goes beyond screen readers. This guide dives into cognitive accessibility — designing for users with ADHD, autism spectrum, and dyslexia through motion, clarity, and focus management.',
            'author' => 'Sarah Chen',
            'imageUrl' => 'https://uxplanet.org/images/a11y-neurodivergent.png',
            'isRead' => true,
            'publishedAt' => '-1 day',
        ],
        'article-ux-planet-2' => [
            'feed' => 'feed-ux-planet',
            'guid' => 'https://uxplanet.org/ai-generated-ui-pitfalls/',
            'title' => 'The 7 Pitfalls of AI-Generated UI Components',
            'url' => 'https://uxplanet.org/ai-generated-ui-pitfalls/',
            'summary' => 'AI can generate UI fast, but these common mistakes will tank your UX.',
            'content' => null,
            'author' => 'Liam Park',
            'imageUrl' => null,
            'isRead' => false,
            'publishedAt' => '-12 hours',
        ],
        'article-mit-1' => [
            'feed' => 'feed-mit-review',
            'guid' => 'https://www.technologyreview.com/2026/01/quantum-computing-real/',
            'title' => 'Quantum Computing Is Closer Than You Think — MIT Says by 2028',
            'url' => 'https://www.technologyreview.com/2026/01/quantum-computing-real/',
            'summary' => 'New error-correction breakthroughs bring fault-tolerant quantum machines within reach.',
            'content' => 'Researchers at MIT have demonstrated a quantum error-correction scheme that reduces decoherence by 99.7%, paving the way for practical quantum computers within the decade.',
            'author' => 'David Feldman',
            'imageUrl' => 'https://www.technologyreview.com/images/quantum.jpg',
            'isRead' => false,
            'publishedAt' => '-3 days',
        ],
        'article-mit-2' => [
            'feed' => 'feed-mit-review',
            'guid' => 'https://www.technologyreview.com/2026/01/synthetic-biology/',
            'title' => 'Synthetic Biology Is Rewriting the Rules of Medicine',
            'url' => 'https://www.technologyreview.com/2026/01/synthetic-biology/',
            'summary' => 'Engineered cells are being used to produce insulin, fight cancer, and more.',
            'content' => null,
            'author' => 'Emily Blakely',
            'imageUrl' => null,
            'isRead' => true,
            'publishedAt' => '-4 days',
        ],
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::ARTICLES as $key => $data) {
            $article = new Article();
            $article->setGuid($data['guid']);
            $article->setTitle($data['title']);
            $article->setUrl($data['url']);
            $article->setSummary($data['summary']);
            $article->setContent($data['content']);
            $article->setAuthor($data['author']);
            $article->setImageUrl($data['imageUrl']);
            $article->setIsRead($data['isRead']);
            $article->setPublishedAt(new DateTimeImmutable($data['publishedAt']));
            $article->setFeed($this->getReference($data['feed'], \App\Entity\Feed::class));

            $manager->persist($article);
            $this->addReference($key, $article);
        }

        $manager->flush();
    }

    public function getOrder(): int
    {
        return 3;
    }
}
