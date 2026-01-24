# Feed Domain Context

## Overview
The Feed domain handles RSS feed management: adding feeds, crawling content, and organizing by categories.

---

## Business Rules

1. **Feed URL must be unique** - No duplicate feeds allowed
2. **Feed must belong to a category** - Orphan feeds not permitted
3. **Crawling is async** - Never block on HTTP requests
4. **Default crawl frequency: 60 minutes** - Configurable per feed
5. **Inactive feeds are not crawled** - Use `isActive` flag

---

## Entities

### Feed
```
- id: UUID
- url: string (unique)
- title: string (nullable, populated after first crawl)
- description: string (nullable)
- categoryId: UUID (required)
- lastCrawledAt: DateTime (nullable)
- crawlFrequencyMinutes: int (default: 60)
- isActive: bool (default: true)
- createdAt: DateTime
- updatedAt: DateTime
```

### Category
```
- id: UUID
- name: string
- description: string (nullable)
- createdAt: DateTime
- updatedAt: DateTime
```

---

## Commands

| Command | Purpose | Handler |
|---------|---------|---------|
| `CreateFeedCommand` | Add new RSS feed | Creates feed, dispatches crawl |
| `UpdateFeedCommand` | Modify feed settings | Updates crawl frequency, etc. |
| `DeleteFeedCommand` | Remove feed | Cascades to articles |
| `CreateCategoryCommand` | Add category | - |
| `UpdateCategoryCommand` | Rename category | - |
| `DeleteCategoryCommand` | Remove category | Fails if feeds exist |

---

## Async Messages

| Message | Handler | Trigger |
|---------|---------|---------|
| `CrawlFeedMessage` | Fetches RSS, creates articles | After feed created, scheduled |
| `ParseFeedContentMessage` | Extracts full article content | After article created |

---

## Crawl Pipeline

```
CrawlFeedMessage
    ↓
Fetch RSS XML
    ↓
Parse items (title, link, pubDate, description)
    ↓
For each item:
    - Check if article exists (by externalId)
    - Create Article if new
    - Dispatch ParseFeedContentMessage
    ↓
Update Feed.lastCrawledAt
```

---

## Error Handling (RFC 7807)

| Error | Exception | Problem Type | Status |
|-------|-----------|--------------|--------|
| Feed URL unreachable | `FeedUnreachableException` | `/problems/unprocessable` | 422 |
| Invalid RSS format | `InvalidFeedFormatException` | `/problems/validation-error` | 400 |
| Category not found | `CategoryNotFoundException` | `/problems/not-found` | 404 |
| Duplicate feed URL | `FeedAlreadyExistsException` | `/problems/conflict` | 409 |

### Example Response
```json
{
  "type": "https://signalist.app/problems/not-found",
  "title": "Category Not Found",
  "status": 404,
  "detail": "The category with ID abc-123 was not found",
  "instance": "/api/v1/feeds"
}
```

---

## API Endpoints

| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/v1/feeds` | List all feeds |
| POST | `/api/v1/feeds` | Create feed |
| GET | `/api/v1/feeds/{id}` | Get feed details |
| PUT | `/api/v1/feeds/{id}` | Update feed |
| DELETE | `/api/v1/feeds/{id}` | Delete feed |
| POST | `/api/v1/feeds/{id}/crawl` | Trigger manual crawl |
| GET | `/api/v1/categories` | List categories |
| POST | `/api/v1/categories` | Create category |

---

## Related Domains

- **Article**: Created by feed crawling
- **Search**: Articles indexed for semantic search
