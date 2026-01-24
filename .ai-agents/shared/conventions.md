# Signalist Conventions

Coding standards, naming rules, and development practices all agents must follow.

---

## PHP Conventions

### File Header
Every PHP file MUST start with:
```php
<?php

declare(strict_types=1);

namespace App\...;
```

### Class Design
```php
// Use readonly for immutable classes
final readonly class CreateFeedCommand
{
    // Constructor property promotion
    public function __construct(
        public string $url,
        public string $categoryId,
    ) {}
}

// Handlers are readonly with interface dependencies
final readonly class CreateFeedHandler
{
    public function __construct(
        private FeedRepositoryInterface $feedRepo,  // Interface, not implementation
        private MessageBusInterface $bus,
    ) {}

    public function __invoke(CreateFeedCommand $command): string
    {
        // Implementation
    }
}
```

### Type Safety
- **Always** use strict scalar types
- **Never** use `mixed` or `array` without docblock
- **Prefer** typed properties over docblocks
- **Use** union types sparingly

```php
// Good
public function find(string $id): ?Feed

// Bad
public function find($id)
```

---

## Naming Conventions

### Classes

| Type | Pattern | Example |
|------|---------|---------|
| Command | `{Verb}{Noun}Command` | `CreateFeedCommand` |
| Query | `{Noun}{Detail}Query` | `FeedListQuery`, `ArticleDetailQuery` |
| Handler | `{Command/Query}Handler` | `CreateFeedHandler` |
| InputDTO | `{Action}{Noun}Input` | `CreateFeedInput` |
| OutputDTO | `{Noun}Output` | `FeedOutput`, `ArticleOutput` |
| Controller | `{Action}{Noun}Controller` | `CreateFeedController` |
| Repository | `{Entity}RepositoryInterface` | `FeedRepositoryInterface` |
| Exception | `{Noun}{Problem}Exception` | `FeedNotFoundException` |
| Message | `{Verb}{Noun}Message` | `CrawlFeedMessage` |
| Event | `{Noun}{PastVerb}Event` | `FeedCreatedEvent` |

### Methods

| Context | Pattern | Example |
|---------|---------|---------|
| Handler entry | `__invoke` | `public function __invoke(Command $cmd)` |
| Repository fetch one | `get`, `find` | `get($id)`, `findByUrl($url)` |
| Repository fetch many | `findBy{Criteria}` | `findByCategory($catId)` |
| Repository persist | `save`, `delete` | `save($entity)` |
| Boolean check | `is{Condition}`, `has{Thing}` | `isActive()`, `hasTags()` |
| Factory | `create`, `from` | `Feed::create(...)` |

### Variables

```php
// Descriptive, no abbreviations
$feedRepository    // Not: $feedRepo, $fr
$categoryId        // Not: $catId, $cid
$articleContent    // Not: $content (too generic)

// Collections are plural
$articles = $repository->findByFeed($feedId);
$tags = $article->getTags();

// Booleans read as questions
$isActive = true;
$hasEmbedding = false;
$shouldNotify = true;
```

### Files & Directories

```
src/Domain/Feed/
├── Command/
│   └── CreateFeedCommand.php      # PascalCase, matches class
├── Handler/
│   └── CreateFeedHandler.php
├── DTO/
│   ├── Input/
│   │   └── CreateFeedInput.php
│   └── Output/
│       └── FeedOutput.php
└── Port/
    └── FeedRepositoryInterface.php
```

---

## Naming Anti-Patterns

### Avoid Generic Names
```php
// Bad
class FeedService {}      // What does it do?
class FeedManager {}      // Meaningless
class FeedHelper {}       // Too vague
class FeedUtils {}        // Not a thing

// Good
class FeedCrawler {}      // Crawls feeds
class FeedValidator {}    // Validates feeds
class FeedParser {}       // Parses feed XML
```

### Avoid Redundant Prefixes/Suffixes
```php
// Bad
interface IFeedRepository {}     // Hungarian notation
class FeedRepositoryImpl {}      // Impl suffix
class AbstractFeed {}            // In filename

// Good
interface FeedRepositoryInterface {}
class DoctrineFeedRepository {}
abstract class BaseFeed {}
```

---

## Testing Conventions

### Test Naming
Pattern: `test{Method}_{Scenario}_{Expected}`

```php
public function testInvoke_ValidUrl_ReturnsFeedId(): void
public function testInvoke_InvalidCategory_ThrowsNotFoundException(): void
public function testInvoke_DuplicateUrl_ThrowsConflictException(): void
```

### Test Structure (AAA)
```php
public function testInvoke_ValidData_CreatesFeed(): void
{
    // Arrange
    $categoryId = Uuid::uuid4()->toString();
    $category = $this->createMock(Category::class);

    $categoryRepo = $this->createMock(CategoryRepositoryInterface::class);
    $categoryRepo->method('get')->with($categoryId)->willReturn($category);

    $feedRepo = $this->createMock(FeedRepositoryInterface::class);
    $feedRepo->expects($this->once())->method('save');

    $handler = new CreateFeedHandler($feedRepo, $categoryRepo, $this->bus);

    // Act
    $result = $handler(new CreateFeedCommand('https://example.com/feed', $categoryId));

    // Assert
    $this->assertTrue(Uuid::isValid($result));
}
```

### Test File Location
Mirror `src/` structure in `tests/`:
```
src/Domain/Feed/Handler/CreateFeedHandler.php
tests/Unit/Domain/Feed/Handler/CreateFeedHandlerTest.php

src/Infrastructure/Persistence/DoctrineFeedRepository.php
tests/Integration/Infrastructure/Persistence/DoctrineFeedRepositoryTest.php
```

---

## Git Conventions

### Commit Messages
Follow **Conventional Commits v1.0.0**:

```
<type>(<scope>): <description>

[optional body]

[optional footer]
```

### Types

| Type | Use For |
|------|---------|
| `feat` | New feature |
| `fix` | Bug fix |
| `refactor` | Code change that neither fixes nor adds |
| `test` | Adding or updating tests |
| `docs` | Documentation only |
| `chore` | Build, CI, dependencies |
| `perf` | Performance improvement |
| `style` | Formatting, no code change |

### Scopes (by domain)
`feed`, `article`, `search`, `bookmark`, `newsletter`, `spotlight`, `infra`, `ui`

### Examples
```bash
feat(feed): add RSS validation before save
fix(newsletter): correct word count calculation
refactor(search): extract embedding generator to separate class
test(bookmark): add integration tests for Raindrop sync
docs(readme): update installation instructions
chore(deps): upgrade symfony/messenger to 8.1
perf(search): add pgvector index for cosine similarity
```

### Branch Naming
```
feature/feed-validation
fix/newsletter-word-count
refactor/search-embedding
```

---

## Documentation Conventions

### PHPDoc (Use Sparingly)
Only add PHPDoc when types can't express the intent:

```php
// Unnecessary - types are clear
/** @param string $id */
public function get(string $id): ?Feed

// Useful - explains business rule
/**
 * @param string $id Feed UUID
 * @return Feed|null Returns null if feed was soft-deleted
 */
public function get(string $id): ?Feed

// Useful - complex return type
/**
 * @return array<string, array{title: string, url: string, score: float}>
 */
public function searchSimilar(string $query): array
```

### Inline Comments
```php
// Good - explains WHY
// Skip feeds that haven't been crawled in 24h to avoid rate limits
if ($feed->getLastCrawledAt() > $threshold) {
    continue;
}

// Bad - explains WHAT (code is self-documenting)
// Loop through feeds
foreach ($feeds as $feed) {
```

---

## API Conventions

### Endpoints
```
GET    /api/v1/feeds              # List
POST   /api/v1/feeds              # Create
GET    /api/v1/feeds/{id}         # Read
PUT    /api/v1/feeds/{id}         # Full update
PATCH  /api/v1/feeds/{id}         # Partial update
DELETE /api/v1/feeds/{id}         # Delete

GET    /api/v1/feeds/{id}/articles   # Nested resource
POST   /api/v1/search                # Action (not a resource)
```

### Response Format
```json
// Success (single)
{
  "id": "uuid",
  "title": "Feed Title",
  "url": "https://..."
}

// Success (collection)
{
  "data": [...],
  "meta": {
    "total": 100,
    "page": 1,
    "limit": 20
  }
}

// Error
{
  "error": {
    "code": "FEED_NOT_FOUND",
    "message": "Feed with ID xyz not found"
  }
}
```

### Status Codes
| Code | Use For |
|------|---------|
| 200 | Success (GET, PUT, PATCH) |
| 201 | Created (POST) |
| 204 | No Content (DELETE) |
| 400 | Bad Request (validation) |
| 401 | Unauthorized |
| 403 | Forbidden |
| 404 | Not Found |
| 409 | Conflict (duplicate) |
| 422 | Unprocessable (business rule) |
| 500 | Internal Error |
