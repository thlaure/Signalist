<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/**
 * Stores vector embeddings for semantic search.
 * Uses pgvector extension with 1536-dimensional vectors (OpenAI ada-002 compatible).
 */
#[ORM\Entity]
#[ORM\Table(name: 'article_embedding')]
#[ORM\Index(name: 'idx_article_embedding_article', columns: ['article_id'])]
class ArticleEmbedding
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    /**
     * The embedding vector stored as JSON array.
     * Note: For production, use a custom DBAL type for pgvector's vector type.
     * This is a placeholder for Phase 2 AI integration.
     *
     * @var array<int, float>|null
     */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $embedding = null;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
    private int $chunkIndex = 0;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $chunkText = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $createdAt;

    #[ORM\ManyToOne(targetEntity: Article::class, inversedBy: 'embeddings')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Article $article;

    public function __construct()
    {
        $this->id = Uuid::v7();
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    /** @return array<int, float>|null */
    public function getEmbedding(): ?array
    {
        return $this->embedding;
    }

    /** @param array<int, float>|null $embedding */
    public function setEmbedding(?array $embedding): self
    {
        $this->embedding = $embedding;

        return $this;
    }

    public function getChunkIndex(): int
    {
        return $this->chunkIndex;
    }

    public function setChunkIndex(int $chunkIndex): self
    {
        $this->chunkIndex = $chunkIndex;

        return $this;
    }

    public function getChunkText(): ?string
    {
        return $this->chunkText;
    }

    public function setChunkText(?string $chunkText): self
    {
        $this->chunkText = $chunkText;

        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getArticle(): Article
    {
        return $this->article;
    }

    public function setArticle(Article $article): self
    {
        $this->article = $article;

        return $this;
    }
}
