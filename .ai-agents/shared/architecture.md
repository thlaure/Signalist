# Signalist Architecture

This document defines the architectural patterns all agents must follow.

---

## Core Principles

1. **Hexagonal Architecture** (Ports & Adapters)
2. **CQRS** (Command Query Responsibility Segregation)
3. **Domain-Driven Design** (Bounded Contexts)
4. **SOLID** Principles
5. **Async-First** (Non-blocking operations)

---

## Hexagonal Architecture

```
                    ┌─────────────────────────────────────────┐
                    │              DOMAIN                      │
                    │  ┌─────────────────────────────────┐    │
    Adapters        │  │         Business Logic          │    │        Adapters
   (Driving)        │  │    (Handlers, Domain Models)    │    │       (Driven)
                    │  └─────────────────────────────────┘    │
 ┌──────────┐       │         ▲              ▲                │       ┌──────────┐
 │Controller│──────►│ ┌───────┴───┐    ┌─────┴─────┐          │◄──────│Repository│
 └──────────┘       │ │   Port    │    │   Port    │          │       └──────────┘
                    │ │(InputDTO) │    │(Interface)│          │
 ┌──────────┐       │ └───────────┘    └───────────┘          │       ┌──────────┐
 │   CLI    │──────►│                                         │◄──────│ External │
 └──────────┘       │                                         │       │   API    │
                    │                                         │       └──────────┘
 ┌──────────┐       │                                         │       ┌──────────┐
 │   MCP    │──────►│                                         │◄──────│   LLM    │
 └──────────┘       └─────────────────────────────────────────┘       └──────────┘
```

### Key Rules
- Domain layer has NO dependencies on infrastructure
- Adapters depend on domain, never the reverse
- Ports (interfaces) defined in domain, implemented in infrastructure

---

## CQRS Pattern

### Command Flow (Write Operations)
```
HTTP Request
    ↓
Controller (validates, creates command)
    ↓
InputDTO (structural validation)
    ↓
Command (immutable intent object)
    ↓
Handler (business logic, domain rules)
    ↓
Domain Model (state changes)
    ↓
Repository Interface (port)
    ↓
Repository Implementation (adapter)
    ↓
Database
```

### Query Flow (Read Operations)
```
HTTP Request
    ↓
Controller (creates query)
    ↓
Query (read intent)
    ↓
QueryHandler (fetches data)
    ↓
Read Model / Repository
    ↓
OutputDTO (response shaping)
    ↓
HTTP Response
```

---

## Directory Mapping

```
src/
├── Domain/                      # HEXAGON CORE
│   └── {DomainName}/
│       ├── Command/             # Write intents
│       ├── Query/               # Read intents
│       ├── Handler/             # Business logic (ONLY place)
│       ├── DTO/
│       │   ├── Input/           # Request validation
│       │   └── Output/          # Response shaping
│       ├── Model/               # Domain entities & value objects
│       ├── Port/                # Interfaces (repository, services)
│       ├── Event/               # Domain events
│       └── Exception/           # Domain-specific exceptions
│
├── Infrastructure/              # ADAPTERS (DRIVEN)
│   ├── Persistence/
│   │   └── Doctrine/            # Repository implementations
│   ├── AI/                      # LLM client adapters
│   ├── RSS/                     # Feed parser adapters
│   ├── MCP/                     # MCP server implementation
│   └── External/                # Third-party API adapters
│
├── UI/                          # ADAPTERS (DRIVING)
│   ├── Controller/              # HTTP controllers
│   ├── Command/                 # CLI commands
│   └── MCP/                     # MCP tool handlers
│
└── Entity/                      # Doctrine ORM mappings
```

---

## Component Responsibilities

### Command
- Immutable data container
- Represents user intent for write operation
- Contains only data, no behavior
- Named as verb: `CreateFeed`, `UpdateCategory`, `DeleteBookmark`

```php
final readonly class CreateFeedCommand
{
    public function __construct(
        public string $url,
        public string $categoryId,
    ) {
    }
}
```

### Query
- Immutable data container
- Represents user intent for read operation
- May contain filters, pagination
- Named as noun: `FeedList`, `ArticleDetail`, `SearchResults`

```php
final readonly class FeedListQuery
{
    public function __construct(
        public ?string $categoryId = null,
        public int $page = 1,
        public int $limit = 20,
    ) {
    }
}
```

### Handler
- **ONLY place for business logic**
- Orchestrates domain operations
- Invokes repositories through interfaces (ports)
- Returns primitive or DTO (never entity)
- One handler per command/query

```php
final readonly class CreateFeedHandler
{
    public function __construct(
        private FeedRepositoryInterface $feedRepo,    // Port
        private CategoryRepositoryInterface $catRepo, // Port
        private MessageBusInterface $bus,
    ) {
    }

    public function __invoke(CreateFeedCommand $command): string
    {
        // Business logic HERE
        $category = $this->catRepo->get($command->categoryId)
            ?? throw new CategoryNotFoundException($command->categoryId);

        $feed = Feed::create($command->url, $category);
        $this->feedRepo->save($feed);
        $this->bus->dispatch(new CrawlFeedMessage($feed->getId()));

        return $feed->getId();
    }
}
```

### InputDTO
- Request payload validation only
- Uses Symfony Validator attributes
- No behavior, no transformation

```php
final readonly class CreateFeedInput
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

### OutputDTO
- Response shaping only
- English field names (API contract)
- No behavior
- May aggregate data from multiple sources

```php
final readonly class FeedOutput
{
    public function __construct(
        public string $id,
        public string $url,
        public string $title,
        public string $categoryId,
        public string $categoryName,
        public \DateTimeInterface $lastCrawledAt,
        public int $articleCount,
    ) {
    }
}
```

### Controller
- HTTP adapter only
- No business logic
- Maps request → DTO → command → response

```php
#[Route('/api/v1/feeds', methods: [Response::METHOD_POST])]
final readonly class CreateFeedController
{
    public function __construct(
        private CreateFeedHandler $handler,
    ) {
    }

    public function __invoke(
        #[MapRequestPayload] CreateFeedInput $input
    ): JsonResponse {
        $id = ($this->handler)(new CreateFeedCommand(
            $input->url,
            $input->categoryId
        ));

        return new JsonResponse(['id' => $id], Response::HTTP_CREATED);
    }
}
```

### Repository Interface (Port)
- Defined in domain
- Persistence abstraction
- No implementation details

```php
interface FeedRepositoryInterface
{
    public function get(string $id): ?Feed;
    public function save(Feed $feed): void;
    public function delete(Feed $feed): void;
    public function findByCategory(string $categoryId): array;
}
```

### Repository Implementation (Adapter)
- Implements port interface
- Lives in Infrastructure
- Contains Doctrine/SQL specifics

```php
final readonly class DoctrineFeedRepository implements FeedRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function get(string $id): ?Feed
    {
        return $this->entityManager->find(Feed::class, $id);
    }

    public function save(Feed $feed): void
    {
        $this->entityManager->persist($feed);
        $this->entityManager->flush();
    }
}
```

---

## Domain Isolation Rules

### NEVER
- Import from another domain directly
- Share entities between domains
- Call another domain's handler directly

### ALLOWED
- Share via domain events
- Share via shared kernel (common value objects)
- Coordinate through application layer

```
Domain A                    Domain B
    │                           │
    │  ──── Event Bus ────►     │
    │                           │
    └───────────────────────────┘
              Shared Kernel
           (Value Objects, IDs)
```

---

## Async Processing

All heavy operations MUST be async:

```php
// In Handler - dispatch to queue
$this->bus->dispatch(new CrawlFeedMessage($feed->getId()));
$this->bus->dispatch(new GenerateEmbeddingsMessage($article->getId()));
$this->bus->dispatch(new SendNewsletterMessage($newsletter->getId()));

// Message Handler (runs in worker)
#[AsMessageHandler]
final readonly class CrawlFeedMessageHandler
{
    public function __invoke(CrawlFeedMessage $message): void
    {
        // Heavy operation here (HTTP calls, AI inference)
    }
}
```

### Must Be Async
- RSS feed crawling
- Content extraction (Readability)
- Embedding generation
- LLM inference (summaries, tagging)
- Email sending
- External API calls (Raindrop.io)

---

## Error Handling Strategy (RFC 7807)

All errors follow **RFC 7807 Problem Details for HTTP APIs**.

```
Domain Exception (extends ProblemException)
    ↓
Caught by ProblemDetailsExceptionListener
    ↓
Converted to Problem Details JSON
    ↓
Response with Content-Type: application/problem+json
```

### Exception Hierarchy
```php
ProblemException (abstract base)
├── NotFoundException
├── ValidationException
├── ConflictException
├── UnauthorizedException
├── ForbiddenException
├── QuotaExceededException
├── UnprocessableException
└── InternalException
```

### Exception to Problem Mapping

| Exception Type | Problem Type | Status |
|----------------|--------------|--------|
| `NotFoundException` | `/problems/not-found` | 404 |
| `ValidationException` | `/problems/validation-error` | 400 |
| `ConflictException` | `/problems/conflict` | 409 |
| `UnauthorizedException` | `/problems/unauthorized` | 401 |
| `ForbiddenException` | `/problems/forbidden` | 403 |
| `QuotaExceededException` | `/problems/quota-exceeded` | 402 |
| `UnprocessableException` | `/problems/unprocessable` | 422 |
| `InternalException` | `/problems/internal-error` | 500 |

### Problem Details Response
```json
{
  "type": "https://signalist.app/problems/not-found",
  "title": "Feed Not Found",
  "status": 404,
  "detail": "The feed with ID 550e8400-e29b-41d4-a716-446655440000 was not found",
  "instance": "/api/v1/feeds/550e8400-e29b-41d4-a716-446655440000"
}
```

### Creating Domain Exceptions
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

// Usage in Handler
$feed = $this->feedRepo->get($id)
    ?? throw new FeedNotFoundException($id);
```
