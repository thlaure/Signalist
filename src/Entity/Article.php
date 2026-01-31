<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'article')]
#[ORM\UniqueConstraint(name: 'uniq_article_feed_guid', columns: ['feed_id', 'guid'])]
#[ORM\Index(name: 'idx_article_published_at', columns: ['published_at'])]
#[ORM\Index(name: 'idx_article_is_read', columns: ['is_read'])]
class Article
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\Column(length: 512)]
    private string $guid;

    #[ORM\Column(length: 500)]
    private string $title;

    #[ORM\Column(length: 2048)]
    private string $url;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $summary = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $content = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $author = null;

    #[ORM\Column(length: 2048, nullable: true)]
    private ?string $imageUrl = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $isRead = false;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $publishedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $createdAt;

    #[ORM\ManyToOne(targetEntity: Feed::class, inversedBy: 'articles')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Feed $feed;

    #[ORM\OneToOne(targetEntity: Bookmark::class, mappedBy: 'article')]
    private ?Bookmark $bookmark = null;

    /** @var Collection<int, ArticleEmbedding> */
    #[ORM\OneToMany(targetEntity: ArticleEmbedding::class, mappedBy: 'article', orphanRemoval: true)]
    private Collection $embeddings;

    public function __construct()
    {
        $this->id = Uuid::v7();
        $this->createdAt = new DateTimeImmutable();
        $this->embeddings = new ArrayCollection();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getGuid(): string
    {
        return $this->guid;
    }

    public function setGuid(string $guid): self
    {
        $this->guid = $guid;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function setSummary(?string $summary): self
    {
        $this->summary = $summary;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(?string $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(?string $imageUrl): self
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }

    public function isRead(): bool
    {
        return $this->isRead;
    }

    public function setIsRead(bool $isRead): self
    {
        $this->isRead = $isRead;

        return $this;
    }

    public function getPublishedAt(): ?DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?DateTimeImmutable $publishedAt): self
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getFeed(): Feed
    {
        return $this->feed;
    }

    public function setFeed(Feed $feed): self
    {
        $this->feed = $feed;

        return $this;
    }

    public function getBookmark(): ?Bookmark
    {
        return $this->bookmark;
    }

    /** @return Collection<int, ArticleEmbedding> */
    public function getEmbeddings(): Collection
    {
        return $this->embeddings;
    }
}
