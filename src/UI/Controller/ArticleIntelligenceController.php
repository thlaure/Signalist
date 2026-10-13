<?php

declare(strict_types=1);

namespace App\UI\Controller;

use App\Domain\AI\Message\EnrichArticleMessage;
use App\Domain\AI\Port\ArticleIntelligenceInterface;
use App\Domain\Article\Handler\GetArticleHandler;
use App\Domain\Article\Query\GetArticleQuery;
use App\Domain\Privacy\Exception\ConsentRequiredException;
use App\Domain\Privacy\Service\ConsentPolicy;
use App\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final readonly class ArticleIntelligenceController
{
    public function __construct(private GetArticleHandler $getArticle, private ConsentPolicy $consent, private MessageBusInterface $bus, private ArticleIntelligenceInterface $intelligence)
    {
    }

    #[Route('/api/v1/articles/{id}/enrich', methods: ['POST'])]
    public function enrich(string $id, #[CurrentUser] User $user): JsonResponse
    {
        ($this->getArticle)(new GetArticleQuery($id, $user->getId()->toRfc4122()));

        if (!$this->consent->allows($user, 'external_ai')) {
            throw new ConsentRequiredException('external_ai');
        }

        if (!$this->intelligence->configured()) {
            throw new ServiceUnavailableHttpException(null, 'AI provider setup is pending.');
        }
        $this->bus->dispatch(new EnrichArticleMessage($id));

        return new JsonResponse(['status' => 'queued'], Response::HTTP_ACCEPTED);
    }
}
