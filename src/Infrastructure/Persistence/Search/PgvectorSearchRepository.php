<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Search;

use App\Domain\Search\Port\SearchRepositoryInterface;
use App\Entity\User;
use App\Infrastructure\Persistence\Type\VectorType;
use Doctrine\DBAL\Connection;

final readonly class PgvectorSearchRepository implements SearchRepositoryInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function fulltext(User $owner, string $query): array
    {
        $ids = $this->connection->fetchFirstColumn(<<<'SQL'
            SELECT a.id FROM article a JOIN feed f ON f.id = a.feed_id
            WHERE f.organization_id IS NULL AND f.owner_id = :owner AND to_tsvector('simple', a.title || ' ' || COALESCE(a.summary, '') || ' ' || COALESCE(a.ai_summary, '')) @@ plainto_tsquery('simple', :query)
            ORDER BY ts_rank_cd(to_tsvector('simple', a.title || ' ' || COALESCE(a.summary, '') || ' ' || COALESCE(a.ai_summary, '')), plainto_tsquery('simple', :query)) DESC, a.id LIMIT 50
            SQL, ['owner' => $owner->getId()->toRfc4122(), 'query' => $query]);

        return array_values(array_filter($ids, is_string(...)));
    }

    public function semantic(User $owner, array $vector): array
    {
        $serialized = new VectorType()->convertToDatabaseValue($vector, $this->connection->getDatabasePlatform());
        $ids = $this->connection->fetchFirstColumn(<<<'SQL'
            SELECT a.id FROM article a JOIN feed f ON f.id = a.feed_id JOIN article_embedding e ON e.article_id = a.id
            WHERE f.organization_id IS NULL AND f.owner_id = :owner AND e.embedding IS NOT NULL
            GROUP BY a.id HAVING MIN(e.embedding <=> CAST(:vector AS vector)) <= 0.45
            ORDER BY MIN(e.embedding <=> CAST(:vector AS vector)), a.id LIMIT 50
            SQL, ['owner' => $owner->getId()->toRfc4122(), 'vector' => $serialized]);

        return array_values(array_filter($ids, is_string(...)));
    }
}
