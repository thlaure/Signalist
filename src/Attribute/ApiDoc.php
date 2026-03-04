<?php

declare(strict_types=1);

namespace App\Attribute;

use Attribute;

/**
 * Documents an API route directly on its controller.
 * For human readability and tooling — not wired to OpenAPI generation.
 *
 * Usage:
 *
 *   #[ApiDoc(
 *       method: 'POST',
 *       path: '/api/v1/feeds',
 *       description: 'Add a new RSS feed and dispatch an async crawl job.',
 *       auth: true,
 *       request: ['url' => 'string (URL)', 'categoryId' => 'string (UUID)'],
 *       responses: [
 *           201 => '{ id: string (UUID) }',
 *           400 => 'Validation error',
 *           401 => 'Unauthenticated',
 *           404 => 'Category not found',
 *           409 => 'Feed URL already exists',
 *       ],
 *   )]
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class ApiDoc
{
    /**
     * @param string $method HTTP method (GET, POST, PUT, PATCH, DELETE)
     * @param string $path Route path (e.g. /api/v1/feeds)
     * @param string $description What the endpoint does
     * @param bool $auth Whether a JWT Bearer token is required
     * @param array<string,string> $request Request body fields and their types
     * @param array<int,string> $responses Map of HTTP status code to response description
     */
    public function __construct(
        public string $method,
        public string $path,
        public string $description,
        public bool $auth = true,
        public array $request = [],
        public array $responses = [],
    ) {
    }
}
