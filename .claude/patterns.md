# Signalist Patterns

Use these patterns as generic guidance. Always prefer nearby repository examples when they exist.

Design intent:

- apply SOLID principles without multiplying abstraction layers
- keep clean architecture and hexagonal boundaries readable
- use native API Platform features directly when they already solve the need cleanly
- keep async side effects explicit for RSS, AI, email, and sync operations
- choose readability over premature optimization
- write code that is easy for a human reviewer to understand

## API Platform State Processor

Use this pattern when API Platform receives the request but business work belongs in a handler.

```php
<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiPlatform\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Domain\Feed\DTO\AddFeedInput;
use App\Domain\Feed\Handler\AddFeedHandler;

final readonly class AddFeedProcessor implements ProcessorInterface
{
    public function __construct(
        private AddFeedHandler $handler,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        \assert($data instanceof AddFeedInput);

        return ($this->handler)($data);
    }
}
```

## Handler / Use Case

```php
<?php

declare(strict_types=1);

namespace App\Domain\Feed\Handler;

use App\Domain\Feed\Command\AddFeedCommand;
use App\Domain\Feed\Message\CrawlFeedMessage;
use App\Domain\Feed\Port\FeedRepositoryInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class AddFeedHandler
{
    public function __construct(
        private FeedRepositoryInterface $repository,
        private MessageBusInterface $messageBus,
    ) {
    }

    public function __invoke(AddFeedCommand $command): void
    {
        $feed = $this->repository->create($command->url, $command->categoryId);

        $this->messageBus->dispatch(new CrawlFeedMessage($feed->getId()));
    }
}
```

## Input DTO Validation

```php
<?php

declare(strict_types=1);

namespace App\Domain\Feed\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class AddFeedInput
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Url]
        public string $url,
        #[Assert\Uuid]
        public string $categoryId,
    ) {
    }
}
```

## Repository Interface

```php
<?php

declare(strict_types=1);

namespace App\Domain\Feed\Port;

use App\Entity\Feed;

interface FeedRepositoryInterface
{
    public function create(string $url, string $categoryId): Feed;

    public function find(string $id): ?Feed;
}
```

## Messenger Message Handler

```php
<?php

declare(strict_types=1);

namespace App\Domain\Feed\MessageHandler;

use App\Domain\Feed\Message\CrawlFeedMessage;
use App\Infrastructure\RSS\FeedCrawler;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class CrawlFeedMessageHandler
{
    public function __construct(
        private FeedCrawler $crawler,
    ) {
    }

    public function __invoke(CrawlFeedMessage $message): void
    {
        $this->crawler->crawl($message->feedId);
    }
}
```

## React Query Hook

```ts
import { useQuery } from '@tanstack/react-query';
import { apiClient } from '../lib/apiClient';

export function useArticles(categoryId?: string) {
  return useQuery({
    queryKey: ['articles', categoryId],
    queryFn: async () => apiClient.getArticles({ categoryId }),
    enabled: undefined !== categoryId,
  });
}
```

## Frontend Component Test

```tsx
import { render, screen } from '@testing-library/react';
import { ArticleCard } from './ArticleCard';

describe('ArticleCard', () => {
  it('renders the article title', () => {
    render(<ArticleCard article={{ id: '1', title: 'Signalist', url: 'https://example.com' }} />);

    expect(screen.getByText('Signalist')).toBeInTheDocument();
  });
});
```
