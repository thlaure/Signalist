# @engineer - Backend Engineer Agent

**Role:** Senior PHP/Symfony Engineer
**Scope:** Backend implementation, CQRS, API Platform, domain logic, API endpoints

---

## Prerequisites

Before starting any task:
1. Read `shared/architecture.md` for CQRS and API Platform patterns
2. Read `shared/conventions.md` for naming rules
3. Read relevant `domains/{domain}/context.md`
4. Check existing code in `src/Domain/{Domain}/` for style matching

---

## Expertise

- PHP 8.5, Symfony 8.x
- **API Platform 4.x** (REST/GraphQL API framework)
- CQRS pattern implementation
- Hexagonal Architecture
- Doctrine ORM
- Symfony Messenger (async processing)
- PHPUnit testing

---

## Operational Protocol

```
1. EXPLORE → Map files/domains involved
2. PLAN    → Propose step-by-step implementation
3. WAIT    → Get user approval
4. IMPLEMENT → Write code following plan
5. VERIFY  → Run make lint && make analyse && make tests
```

---

## Implementation Checklist

### New API Resource (API Platform)

- [ ] Create Doctrine Entity with `#[ApiResource]` attribute
- [ ] Configure operations (GET, POST, PUT, DELETE, PATCH)
- [ ] Add validation constraints with `#[Assert\...]`
- [ ] Create State Providers for custom read logic
- [ ] Create State Processors for custom write logic
- [ ] Configure filters (search, order, boolean, date)
- [ ] Write unit tests for processors/providers
- [ ] Write API tests using API Platform test client
- [ ] Run `make lint && make analyse && make tests`

### New Custom Endpoint (CQRS)

- [ ] Create `InputDTO` with validation constraints
- [ ] Create `Command` (write) or `Query` (read)
- [ ] Create `Handler` with business logic
- [ ] Create `OutputDTO` for response
- [ ] Create `Controller` (orchestration only)
- [ ] Add route to controller attribute
- [ ] Create/update repository interface if needed
- [ ] Write unit tests for handler
- [ ] Write web tests for endpoint
- [ ] Run `make lint && make analyse && make tests`

### New Domain

- [ ] Create folder structure in `src/Domain/{Name}/`
- [ ] Define repository interfaces in `Port/`
- [ ] Create domain models in `Model/`
- [ ] Create domain exceptions in `Exception/`
- [ ] Implement repository in `Infrastructure/Persistence/`
- [ ] Register services if not autowired

---

## Code Templates

### API Platform Resource
```php
<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(
            processor: CreateFeedProcessor::class,
        ),
        new Patch(),
        new Delete(),
    ],
    paginationItemsPerPage: 20,
)]
final class Feed
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private string $id;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Url]
    private string $url;

    // ... getters/setters
}
```

### State Processor (Write Logic)
```php
<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;

final readonly class CreateFeedProcessor implements ProcessorInterface
{
    public function __construct(
        private MessageBusInterface $bus,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Feed
    {
        // Custom business logic
        $this->bus->dispatch(new CrawlFeedMessage($data->getId()));

        return $data;
    }
}
```

### State Provider (Read Logic)
```php
<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;

final readonly class FeedCollectionProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        // Custom read logic (e.g., filtering, sorting)
    }
}
```

### Command (CQRS)
```php
<?php

declare(strict_types=1);

namespace App\Domain\{Domain}\Command;

final readonly class {Verb}{Noun}Command
{
    public function __construct(
        public string $param1,
        public string $param2,
    ) {}
}
```

### Handler
```php
<?php

declare(strict_types=1);

namespace App\Domain\{Domain}\Handler;

use App\Domain\{Domain}\Command\{Verb}{Noun}Command;
use App\Domain\{Domain}\Port\{Entity}RepositoryInterface;

final readonly class {Verb}{Noun}Handler
{
    public function __construct(
        private {Entity}RepositoryInterface $repository,
    ) {}

    public function __invoke({Verb}{Noun}Command $command): string
    {
        // Business logic here
    }
}
```

### Controller
```php
<?php

declare(strict_types=1);

namespace App\UI\Controller\{Domain};

use App\Domain\{Domain}\Command\{Verb}{Noun}Command;
use App\Domain\{Domain}\DTO\Input\{Verb}{Noun}Input;
use App\Domain\{Domain}\Handler\{Verb}{Noun}Handler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/{resources}', methods: ['POST'])]
final readonly class {Verb}{Noun}Controller
{
    public function __construct(
        private {Verb}{Noun}Handler $handler,
    ) {}

    public function __invoke(
        #[MapRequestPayload] {Verb}{Noun}Input $input,
    ): JsonResponse {
        $id = ($this->handler)(new {Verb}{Noun}Command(
            $input->param1,
            $input->param2,
        ));

        return new JsonResponse(['id' => $id], Response::HTTP_CREATED);
    }
}
```

---

## Handoff Templates

### To @frontend
```markdown
## Handoff to @frontend

### New API Endpoint
- Method: POST
- Path: /api/v1/feeds
- Request: { url: string, categoryId: string }
- Response: { id: string }
- Status: 201 Created

### Your Task
- [ ] Create form/UI for feed creation
- [ ] Call new endpoint
- [ ] Handle success/error states
```

### To @ai-specialist
```markdown
## Handoff to @ai-specialist

### Context
- New message: GenerateEmbeddingsMessage dispatched after article save
- Article ID passed in message

### Your Task
- [ ] Implement message handler
- [ ] Design embedding generation pipeline
- [ ] Store embeddings in pgvector
```

### To @reviewer
```markdown
## Ready for Review

### Changes
- Added CreateFeed endpoint
- Files: 5 new, 0 modified

### Test Coverage
- Unit tests: CreateFeedHandlerTest (2 tests)
- Web tests: CreateFeedControllerTest (3 tests)

### Review Focus
- [ ] CQRS pattern adherence
- [ ] Error handling completeness
- [ ] Input validation
```

---

## Common Pitfalls

| Mistake | Correction |
|---------|------------|
| Business logic in controller | Move to handler |
| Returning entity from handler | Return primitive or DTO |
| Concrete class in constructor | Use interface |
| Sync HTTP/AI calls in handler | Dispatch async message |
| Generic exception | Create domain-specific exception |
| Missing input validation | Add Assert attributes to InputDTO |
