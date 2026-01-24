# Agent Project Guide: Signalist

---

# 0. TL;DR (Read This First)

- **Project:** AI-powered RSS intelligence platform (SaaS)
- **Architecture:** Hexagonal Architecture + CQRS + API Platform + SOLID
- **Flow:** `API Platform → State Processor → Handler → Domain Model → Repository`
- **Stack:** PHP 8.4 + Symfony 8.x + API Platform 4.x (Backend) | React + TypeScript + Vite + MUI (Frontend)
- **Server:** **FrankenPHP** (replaces nginx + php-fpm)
- **Database:** PostgreSQL + **pgvector** for embeddings
- **AI:** Symfony AI (OpenAI, Anthropic, Mistral) + MCP Server
- **Queue:** Symfony Messenger + Redis
- **Testing:** PHPUnit (Backend) + Jest (Frontend) - mandatory
- **Process:**
  1. **Explore** context
  2. **Plan** step-by-step & get approval
  3. **Implement** strictly following the plan
  4. **Verify** (Lint, Analyze, Test)

## Quick Start
```bash
make install
# Or manually:
docker compose up -d --build
```

---

# 1. Project Overview

**Signalist** is a smart intelligence platform designed to:
- Aggregate, filter, and synthesize RSS feeds using AI
- Provide a natural language command interface (Spotlight-like, `Cmd+K`)
- Expose data via Model Context Protocol (MCP) for LLM ecosystem integration

## 1.1 Key Features

| Feature | Description |
|---------|-------------|
| **Feed Management** | Categories, RSS aggregation, full content extraction (Readability) |
| **Navigation** | Global dashboard, categorized views, full-text & semantic search |
| **Bookmarking** | Save articles, auto-tagging via LLM, Raindrop.io sync |
| **AI Newsletters** | LLM-generated summaries, configurable reading time (200 wpm), scheduling |
| **Social Sharing** | WhatsApp, X, LinkedIn, Threads, Bluesky integration |
| **Spotlight** | Natural language command bar for CRUD and AI queries |

## 1.2 Project Phases

1. **Phase 1 (MVP):** Core RSS engine, PostgreSQL schema, Dashboard UI
2. **Phase 2 (AI Layer):** Symfony AI for summaries and auto-tagging
3. **Phase 3 (Automation):** Newsletter scheduler, Raindrop.io sync
4. **Phase 4 (Ecosystem):** Spotlight command engine, MCP server

---

# 2. Tech Stack

## 2.1 Backend
- **Language:** PHP **8.4**
- **Framework:** Symfony **8.x** + **API Platform 4.x**
- **Server:** **FrankenPHP** (built on Caddy, worker mode)
- **Architecture:** CQRS, Clean Architecture, Hexagonal (Ports & Adapters)
- **Database:** PostgreSQL + **pgvector** extension
- **Queue:** Symfony Messenger + Redis
- **AI Integration:** Symfony AI (`#[AsTool]` attributes)
- **Protocol:** Model Context Protocol (MCP) Server

## 2.2 Frontend
- **Framework:** React with TypeScript
- **Build:** Vite
- **Components:** MUI (Material UI)
- **Testing:** Jest + React Testing Library

## 2.3 Third-Party Integrations
- **Raindrop.io:** OAuth2 bookmark synchronization
- **Email:** Symfony Mailer
- **LLM Providers:** OpenAI, Anthropic, Mistral (via Symfony AI)

---

# 3. Commands

| Purpose | Command |
|---------|---------|
| Start containers | `docker compose up -d --build` |
| Code style | `make lint` |
| Static analysis | `make analyse` |
| Backend tests | `make tests-unit` |
| Frontend tests | `npm test` |
| All commands | `make help` |

---

# 4. Architecture & File Structure

## 4.1 CQRS Components

| Component | Responsibility |
|-----------|----------------|
| **Query** | Read intent (GET). Returns DTOs via read models. |
| **Command** | Write intent (POST/PUT/DELETE). Encapsulates user intent. |
| **Handler** | Orchestrates domain logic. **Only place for business logic.** |
| **InputDTO** | Request payload validation (strict constraints). |
| **OutputDTO** | Response shaping (read-only, English field names). |
| **Controller** | HTTP Adapter. Maps Request → InputDTO → Command/Query → Response. |

## 4.2 Directory Structure

```
src/
├── Domain/                  # Business logic (vertical slices)
│   ├── Feed/
│   │   ├── Command/
│   │   ├── Query/
│   │   ├── Handler/
│   │   ├── DTO/
│   │   ├── Model/
│   │   └── Port/            # Repository interfaces
│   ├── Category/
│   ├── Article/
│   ├── Bookmark/
│   ├── Newsletter/
│   └── Spotlight/
├── Infrastructure/          # Adapters (implementations)
│   ├── Persistence/         # Doctrine repositories
│   ├── AI/                  # Symfony AI adapters
│   ├── MCP/                 # MCP server implementation
│   ├── RSS/                 # Feed parsers
│   └── External/            # Third-party APIs (Raindrop)
├── UI/                      # Controllers, CLI commands
│   ├── Controller/
│   └── Command/
└── Entity/                  # Doctrine entities
```

## 4.3 Routing Conventions

| Route Type | Prefix | Purpose |
|------------|--------|---------|
| REST API | `/api/v1/` | Main application endpoints |
| MCP | `/mcp/` | Model Context Protocol endpoints |
| Internal | - | Use UUIDs, avoid numeric IDs |

---

# 5. Code Style & Quality

## 5.1 Standards
- Every file: `declare(strict_types=1);`
- Use PHP 8.5 features: `readonly` classes, constructor promotion
- PSR-12 coding standard
- Explicit, descriptive naming (e.g., `RssFeedParser` not `FeedService`)

## 5.2 Naming Examples

**Good:**
- `RssFeedCrawler` - crawls RSS feeds
- `ArticleEmbeddingGenerator` - generates vector embeddings
- `NewsletterContentBuilder` - builds newsletter content

**Bad:**
- `FeedService` - ambiguous
- `AIHelper` - too generic
- `Manager` - meaningless

## 5.3 Maintainability Principles
- **Immutability:** Domain models valid upon construction
- **Async First:** RSS fetching and embedding generation via queues
- **No God Classes:** Single responsibility per service
- **Handlers:** Short, focused, orchestration only

---

# 6. Agent Instructions & Boundaries

## 6.1 ALWAYS DO
- Follow CQRS, Hexagonal Architecture, SOLID
- Validate inputs strictly via InputDTOs
- Run `make lint` + `make analyse` on generated code
- Write tests for every change (PHPUnit/Jest)
- Use async processing for RSS crawling and AI inference
- Ensure AI summaries retain source URLs (factual integrity)
- Use Conventional Commits for git messages

## 6.2 ASK FIRST
- Adding new composer/npm packages
- Changing PostgreSQL schema or pgvector dimensions
- Modifying MCP protocol implementation
- Changing LLM provider configurations

## 6.3 NEVER DO
- Commit to `main` directly
- Hardcode API keys (use environment variables)
- Perform blocking HTTP/AI calls in web request cycle
- Add coupling between domains
- Write business logic in controllers
- Mix frontend and backend logic
- Create "god services"

---

# 7. Exception Handling (RFC 7807)

All API errors follow **RFC 7807 - Problem Details for HTTP APIs**.

## 7.1 Problem Details Format
```json
{
  "type": "https://signalist.app/problems/feed-not-found",
  "title": "Feed Not Found",
  "status": 404,
  "detail": "The feed with ID 550e8400-e29b-41d4-a716-446655440000 was not found",
  "instance": "/api/v1/feeds/550e8400-e29b-41d4-a716-446655440000"
}
```

| Field | Required | Description |
|-------|----------|-------------|
| `type` | Yes | URI identifying the problem type |
| `title` | Yes | Short, human-readable summary |
| `status` | Yes | HTTP status code |
| `detail` | Yes | Human-readable explanation specific to this occurrence |
| `instance` | No | URI reference to the specific occurrence |

## 7.2 Problem Types

| Type URI | Title | Status | When |
|----------|-------|--------|------|
| `/problems/validation-error` | Validation Error | 400 | Input validation failed |
| `/problems/not-found` | Resource Not Found | 404 | Entity doesn't exist |
| `/problems/conflict` | Resource Conflict | 409 | Duplicate or conflict |
| `/problems/unprocessable` | Unprocessable Entity | 422 | Business rule violation |
| `/problems/quota-exceeded` | Quota Exceeded | 402 | Rate/usage limit hit |
| `/problems/internal-error` | Internal Error | 500 | Unexpected server error |

## 7.3 Validation Errors (Extended)
For validation errors, include field-level details:
```json
{
  "type": "https://signalist.app/problems/validation-error",
  "title": "Validation Error",
  "status": 400,
  "detail": "The request body contains invalid data",
  "errors": [
    {
      "field": "url",
      "message": "This value is not a valid URL"
    },
    {
      "field": "categoryId",
      "message": "This value should not be blank"
    }
  ]
}
```

## 7.4 Custom Exceptions
```php
final class FeedNotFoundException extends ProblemException
{
    public function __construct(string $feedId)
    {
        parent::__construct(
            type: 'https://signalist.app/problems/not-found',
            title: 'Feed Not Found',
            status: Response::HTTP_NOT_FOUND,
            detail: sprintf('The feed with ID %s was not found', $feedId),
        );
    }
}
```

## 7.5 Base ProblemException
```php
abstract class ProblemException extends \Exception implements HttpExceptionInterface
{
    public function __construct(
        public readonly string $type,
        public readonly string $title,
        public readonly int $status,
        public readonly string $detail,
        public readonly ?string $instance = null,
        public readonly array $extensions = [],
    ) {
        parent::__construct($detail, $status);
    }

    public function getStatusCode(): int
    {
        return $this->status;
    }

    public function toProblemDetails(): array
    {
        return array_filter([
            'type' => $this->type,
            'title' => $this->title,
            'status' => $this->status,
            'detail' => $this->detail,
            'instance' => $this->instance,
            ...$this->extensions,
        ]);
    }
}
```

## 7.6 Exception Listener
```php
final class ProblemDetailsExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $request = $event->getRequest();

        $problem = match (true) {
            $exception instanceof ProblemException => $exception->toProblemDetails(),
            $exception instanceof HttpExceptionInterface => [
                'type' => 'https://signalist.app/problems/http-error',
                'title' => Response::$statusTexts[$exception->getStatusCode()] ?? 'Error',
                'status' => $exception->getStatusCode(),
                'detail' => $exception->getMessage(),
            ],
            default => [
                'type' => 'https://signalist.app/problems/internal-error',
                'title' => 'Internal Server Error',
                'status' => 500,
                'detail' => $this->env === 'prod' ? 'An unexpected error occurred' : $exception->getMessage(),
            ],
        };

        $problem['instance'] ??= $request->getPathInfo();

        $response = new JsonResponse($problem, $problem['status'], [
            'Content-Type' => 'application/problem+json',
        ]);

        $event->setResponse($response);
    }
}
```

---

# 8. Testing

Testing is **mandatory**. Target 100% coverage on business logic.

## 8.1 Backend (PHPUnit)

| Type | Purpose |
|------|---------|
| **Unit** | Test Handlers, Domain Models. Mock repositories and AI clients. |
| **Integration** | Test Postgres interactions, especially pgvector queries. |
| **Web** | Full HTTP flow validation. |

**Naming:** `test{Method}_{Scenario}_{Expected}`

Example: `testAddFeed_WithInvalidUrl_ThrowsValidationException`

## 8.2 Frontend (Jest/RTL)
- Test React components and hooks
- Mock API calls
- Test Spotlight command parsing

---

# 9. Project Specifics

## 9.1 Vector Search (pgvector)
Pipeline: `RSS Fetch → HTML Clean (Readability) → Chunking → Embedding → Postgres`

```sql
CREATE EXTENSION vector;

CREATE TABLE article_embeddings (
    id UUID PRIMARY KEY,
    article_id UUID REFERENCES articles(id),
    embedding vector(1536),
    chunk_index INT
);

CREATE INDEX ON article_embeddings
USING ivfflat (embedding vector_cosine_ops) WITH (lists = 100);
```

## 9.2 MCP Server (Model Context Protocol)
Implement tools with `#[AsTool]` for LLM accessibility:

```php
#[AsTool(
    name: 'search_articles',
    description: 'Search articles by semantic query'
)]
final class SearchArticlesTool
{
    public function __invoke(
        string $query,
        int $limit = 10,
    ): array {
        // Semantic search implementation
    }
}
```

## 9.3 Newsletter Generation
- Default reading time: 5 minutes
- Calculation: 200 words per minute
- Structure: Grouped by category, clickable titles, concise summaries
- Scheduling: Symfony Scheduler (daily, weekly, custom)

## 9.4 Spotlight Command Interface
- Activation: `Cmd+K`
- Natural Language Processing for intent mapping
- Example: "Add https://example.com/feed to Dev category"

---

# 10. Complete Feature Example

## Example: Add RSS Feed (`POST /api/v1/feeds`)

### 10.1 Input DTO
```php
final readonly class AddFeedInput
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Url]
        public string $url,

        #[Assert\NotBlank]
        #[Assert\Uuid]
        public string $categoryId,
    ) {
    }
}
```

### 10.2 Command
```php
final readonly class AddFeedCommand
{
    public function __construct(
        public string $url,
        public string $categoryId,
    ) {
    }
}
```

### 10.3 Handler
```php
final readonly class AddFeedHandler
{
    public function __construct(
        private FeedRepositoryInterface $feedRepo,
        private CategoryRepositoryInterface $categoryRepo,
        private MessageBusInterface $bus,
    ) {
    }

    public function __invoke(AddFeedCommand $command): string
    {
        $category = $this->categoryRepo->get($command->categoryId)
            ?? throw new CategoryNotFoundException($command->categoryId);

        $feed = Feed::create(
            url: $command->url,
            category: $category
        );

        $this->feedRepo->save($feed);

        // Dispatch async crawl job
        $this->bus->dispatch(new CrawlFeedMessage($feed->getId()));

        return $feed->getId();
    }
}
```

### 10.4 Controller
```php
#[Route('/api/v1/feeds', methods: ['POST'])]
final readonly class AddFeedController
{
    public function __construct(
        private AddFeedHandler $handler,
    ) {
    }

    public function __invoke(
        #[MapRequestPayload] AddFeedInput $input
    ): JsonResponse {
        $id = ($this->handler)(new AddFeedCommand(
            $input->url,
            $input->categoryId
        ));

        return new JsonResponse(['id' => $id], Response::HTTP_CREATED);
    }
}
```

### 10.5 Unit Test
```php
final class AddFeedHandlerTest extends TestCase
{
    public function testInvoke_ValidUrl_DispatchesCrawlJob(): void
    {
        $categoryId = Uuid::uuid4()->toString();
        $category = $this->createMock(Category::class);

        $categoryRepo = $this->createMock(CategoryRepositoryInterface::class);
        $categoryRepo->method('get')->with($categoryId)->willReturn($category);

        $feedRepo = $this->createMock(FeedRepositoryInterface::class);
        $feedRepo->expects($this->once())->method('save');

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects($this->once())->method('dispatch');

        $handler = new AddFeedHandler($feedRepo, $categoryRepo, $bus);
        $result = $handler(new AddFeedCommand('https://example.com/feed', $categoryId));

        $this->assertTrue(Uuid::isValid($result));
    }

    public function testInvoke_InvalidCategory_ThrowsException(): void
    {
        $categoryRepo = $this->createMock(CategoryRepositoryInterface::class);
        $categoryRepo->method('get')->willReturn(null);

        $feedRepo = $this->createMock(FeedRepositoryInterface::class);
        $bus = $this->createMock(MessageBusInterface::class);

        $handler = new AddFeedHandler($feedRepo, $categoryRepo, $bus);

        $this->expectException(CategoryNotFoundException::class);
        $handler(new AddFeedCommand('https://example.com/feed', 'invalid-id'));
    }
}
```

---

# 11. Data Flow Summary

```
Request → Controller
       → InputDTO validation
       → Command/Query creation
       → Handler (business logic)
       → Domain Model operations
       → Repository::save()
       → [Async] Message dispatch (Messenger)
       → Controller returns JSON

Async Worker:
Message → Handler → External API/AI → Repository update
```

---

# 12. Agents

This project uses specialized agents defined in `.ai-agents/`:

| Agent | File | Purpose |
|-------|------|---------|
| `@signalist-engineer` | `engineering.md` | Backend development, CQRS, Symfony |
| `@signalist-frontend` | `frontend.md` | React, TypeScript, MUI components |
| `@signalist-ai` | `ai-integration.md` | Symfony AI, MCP, embeddings |
| `@signalist-infra` | `infrastructure.md` | Docker, PostgreSQL, Redis |
| `@signalist-marketer` | `marketing.md` | Content, growth, positioning |

---

# 13. Git Conventions

Use **Conventional Commits v1.0.0**:

```
feat(feed): add RSS validation before save
fix(newsletter): correct word count calculation
refactor(spotlight): extract command parser
test(bookmark): add integration tests for tagging
docs(readme): update installation steps
```

---

# 14. Key Takeaways

| Concept | Location | Purpose |
|---------|----------|---------|
| Structural validation | InputDTO | Validates incoming data |
| Business validation | Handler | Enforces domain rules |
| Orchestration only | Controller | No business logic |
| Persistence | Repository | Storage operations only |
| Response shaping | OutputDTO | Clean public API |
| Error handling | ExceptionListener | Consistent API errors |
| AI processing | Messenger Workers | Async, non-blocking |
| Semantic search | pgvector | Vector similarity queries |
