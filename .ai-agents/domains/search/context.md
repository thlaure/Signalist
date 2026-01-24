# Search Domain Context

## Overview
The Search domain handles semantic search using pgvector embeddings, allowing users to find articles by meaning rather than exact keywords.

---

## Business Rules

1. **Embeddings are chunked** - Articles split into ~512 token chunks
2. **Overlap for context** - 50 token overlap between chunks
3. **Similarity threshold: 0.7** - Below this, results are too dissimilar
4. **Embeddings are immutable** - Regenerate, don't update
5. **Source URL always included** - Factual integrity requirement

---

## Entities

### ArticleEmbedding
```
- id: UUID
- articleId: UUID
- embedding: vector(1536) -- pgvector type
- chunkIndex: int
- chunkText: text (optional, for debugging)
- createdAt: DateTime
```

---

## Commands

| Command | Purpose | Handler |
|---------|---------|---------|
| `GenerateEmbeddingsCommand` | Create embeddings for article | Chunks and embeds |
| `DeleteEmbeddingsCommand` | Remove embeddings | When article deleted |
| `ReindexArticleCommand` | Regenerate embeddings | If content changed |

---

## Queries

| Query | Purpose |
|-------|---------|
| `SemanticSearchQuery` | Find similar articles |
| `SimilarArticlesQuery` | Find articles similar to given one |

---

## Embedding Pipeline

```
GenerateEmbeddingsMessage
    ↓
Load Article.content
    ↓
Chunk content (ArticleChunker)
    ├── Max 512 tokens per chunk
    └── 50 token overlap
    ↓
For each chunk:
    ├── Generate embedding (OpenAI text-embedding-ada-002)
    └── Store in article_embeddings
```

### Chunking Strategy

```php
class ArticleChunker
{
    private const MAX_TOKENS = 512;
    private const OVERLAP = 50;

    public function chunk(string $content): array
    {
        // 1. Split into sentences
        // 2. Accumulate sentences until MAX_TOKENS
        // 3. Store chunk
        // 4. Keep last OVERLAP tokens for next chunk
        // 5. Repeat
    }
}
```

---

## Similarity Search

### pgvector Operators
- `<=>` - Cosine distance (lower = more similar)
- `<->` - L2 distance
- `<#>` - Inner product

### Query Pattern
```sql
SELECT a.*, 1 - (e.embedding <=> :query_embedding) as similarity
FROM article_embeddings e
JOIN articles a ON a.id = e.article_id
WHERE 1 - (e.embedding <=> :query_embedding) > 0.7
ORDER BY e.embedding <=> :query_embedding
LIMIT 10;
```

### Index Configuration
```sql
-- IVFFlat for approximate search (faster)
CREATE INDEX idx_embeddings_vector ON article_embeddings
    USING ivfflat (embedding vector_cosine_ops)
    WITH (lists = 100);

-- Increase lists as data grows:
-- <100k rows: lists = 100
-- 100k-1M rows: lists = 1000
-- >1M rows: Consider HNSW
```

---

## Error Handling

| Error | Exception | HTTP |
|-------|-----------|------|
| Embedding generation failed | `EmbeddingGenerationException` | - (logged, async) |
| No results found | - | Return empty array |
| Rate limit exceeded | `QuotaExceededException` | 429 |

---

## API Endpoints

| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/v1/search?q={query}` | Semantic search |
| GET | `/api/v1/articles/{id}/similar` | Find similar articles |

---

## MCP Tools

```php
#[AsTool(
    name: 'search_articles',
    description: 'Search articles using semantic similarity'
)]
class SearchArticlesTool
{
    public function __invoke(string $query, int $limit = 10): array;
}
```

---

## Performance Considerations

1. **Index maintenance** - Run `ANALYZE` after bulk inserts
2. **Connection pooling** - pgvector queries can be slow
3. **Caching** - Cache frequent query embeddings
4. **Batch processing** - Generate embeddings in batches

---

## Related Domains

- **Article**: Source content for embeddings
- **Spotlight**: Uses semantic search for queries
