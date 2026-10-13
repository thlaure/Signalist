<?php

declare(strict_types=1);

namespace App\Infrastructure\MCP;

use App\Entity\User;
use Mcp\Schema\ToolAnnotations;
use Mcp\Server;
use Mcp\Server\Session\FileSessionStore;
use stdClass;

final readonly class McpServerFactory
{
    public function __construct(private ArticleTools $tools, private string $cacheDir)
    {
    }

    public function create(User $user): Server
    {
        $readOnly = new ToolAnnotations(readOnlyHint: true, destructiveHint: false, openWorldHint: false);
        $idSchema = ['type' => 'object', 'properties' => ['id' => ['type' => 'string', 'format' => 'uuid']], 'required' => ['id'], 'additionalProperties' => false];

        return Server::builder()->setServerInfo('Signalist', '1.0.0')
            ->setSession(new FileSessionStore($this->cacheDir.'/mcp/'.$user->getId()->toRfc4122()))
            ->addTool($this->tools->searchArticles(...), name: 'search_articles', description: 'Search your articles by text.', annotations: $readOnly, inputSchema: ['type' => 'object', 'properties' => ['query' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 500]], 'required' => ['query'], 'additionalProperties' => false])
            ->addTool($this->tools->getArticle(...), name: 'get_article', description: 'Read article metadata and a minimized summary.', annotations: $readOnly, inputSchema: $idSchema)
            ->addTool($this->tools->getFeedSummary(...), name: 'get_feed_summary', description: 'Read the latest summaries from one of your feeds.', annotations: $readOnly, inputSchema: $idSchema)
            ->addTool($this->tools->listCategories(...), name: 'list_categories', description: 'List your categories.', annotations: $readOnly, inputSchema: ['type' => 'object', 'properties' => new stdClass(), 'additionalProperties' => false])
            ->build();
    }
}
