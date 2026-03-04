# Bookmark Domain Context

## Overview
The Bookmark domain handles saving articles for later, tagging, and synchronization with Raindrop.io.

---

## Business Rules

1. **One bookmark per article** - No duplicates
2. **Tags are shared with Article domain** - Same Tag entity
3. **Raindrop sync is optional** - Only if configured
4. **Raindrop sync is async** - Never block on API calls
5. **Bidirectional sync** (future) - Import from Raindrop

---

## Entities

### Bookmark
```
- id: UUID
- articleId: UUID (unique)
- raindropId: string (nullable, external ID)
- notes: text (nullable, user notes)
- createdAt: DateTime
- updatedAt: DateTime
```

---

## Commands

| Command | Purpose | Handler |
|---------|---------|---------|
| `CreateBookmarkCommand` | Bookmark an article | Creates bookmark, syncs |
| `UpdateBookmarkCommand` | Update notes | - |
| `DeleteBookmarkCommand` | Remove bookmark | Also removes from Raindrop |
| `SyncBookmarkToRaindropCommand` | Push to Raindrop | Async |
| `ImportFromRaindropCommand` | Import bookmarks | Future |

---

## Queries

| Query | Purpose |
|-------|---------|
| `BookmarkListQuery` | List bookmarks with filters |
| `BookmarkDetailQuery` | Single bookmark with article |

---

## Async Messages

| Message | Handler | Trigger |
|---------|---------|---------|
| `SyncBookmarkToRaindropMessage` | Push bookmark to Raindrop API | After bookmark created |
| `DeleteRaindropBookmarkMessage` | Delete from Raindrop | After bookmark deleted |

---

## Raindrop.io Integration

### Authentication
- OAuth2 flow
- Store tokens in user settings (future: multi-tenant)
- Refresh tokens automatically

### API Operations
```
POST /rest/v1/raindrop      # Create bookmark
PUT  /rest/v1/raindrop/{id} # Update bookmark
DELETE /rest/v1/raindrop/{id} # Delete bookmark
GET  /rest/v1/raindrops/0   # List bookmarks
```

### Sync Payload
```json
{
  "link": "https://article-url.com",
  "title": "Article Title",
  "excerpt": "Article summary...",
  "tags": ["tag1", "tag2"],
  "collection": { "$id": 123 }
}
```

---

## Error Handling (RFC 7807)

| Error | Exception | Problem Type | Status |
|-------|-----------|--------------|--------|
| Article not found | `ArticleNotFoundException` | `/problems/not-found` | 404 |
| Already bookmarked | `BookmarkAlreadyExistsException` | `/problems/conflict` | 409 |
| Raindrop sync failed | - | - (logged, async) | - |
| Raindrop not configured | `RaindropNotConfiguredException` | `/problems/unprocessable` | 422 |

---

## API Endpoints

| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/v1/bookmarks` | List bookmarks |
| POST | `/api/v1/bookmarks` | Create bookmark |
| GET | `/api/v1/bookmarks/{id}` | Get bookmark |
| PUT | `/api/v1/bookmarks/{id}` | Update notes |
| DELETE | `/api/v1/bookmarks/{id}` | Delete bookmark |
| POST | `/api/v1/bookmarks/{id}/sync` | Force Raindrop sync |

---

## MCP Tools

```php
#[AsTool(
    name: 'bookmark_article',
    description: 'Save an article to bookmarks with optional tags'
)]
class BookmarkArticleTool
{
    public function __invoke(string $articleId, ?string $tags = null): array;
}
```

---

## Related Domains

- **Article**: Bookmarks reference articles
- **Spotlight**: "Bookmark this" command
