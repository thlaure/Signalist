<?php

declare(strict_types=1);

namespace App\Infrastructure\EventListener;

use App\Infrastructure\Exception\ProblemException;

use function is_int;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\Exception\MissingConstructorArgumentsException;
use Throwable;

/**
 * Converts exceptions to RFC 7807 Problem Details responses.
 */
#[AsEventListener(event: KernelEvents::EXCEPTION, priority: -10)]
final readonly class ProblemDetailsExceptionListener
{
    private const string CONTENT_TYPE = 'application/problem+json';

    private const string BASE_TYPE_URI = 'https://signalist.app/problems';

    public function __construct(
        private string $environment,
    ) {
    }

    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $request = $event->getRequest();

        // Only handle API requests
        if (!str_starts_with($request->getPathInfo(), '/api/')) {
            return;
        }

        $problem = $this->buildProblemDetails($exception, $request->getPathInfo());
        $status = is_int($problem['status']) ? $problem['status'] : Response::HTTP_INTERNAL_SERVER_ERROR;

        $response = new JsonResponse(
            data: $problem,
            status: $status,
            headers: ['Content-Type' => self::CONTENT_TYPE],
        );

        $event->setResponse($response);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildProblemDetails(Throwable $exception, string $path): array
    {
        if ($exception instanceof ProblemException) {
            $problem = $exception->toProblemDetails();
            $problem['instance'] ??= $path;

            return $problem;
        }

        if ($exception instanceof MissingConstructorArgumentsException) {
            return [
                'type' => self::BASE_TYPE_URI . '/validation-error',
                'title' => 'Validation Error',
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'detail' => $exception->getMessage(),
                'instance' => $path,
            ];
        }

        if ($exception instanceof HttpExceptionInterface) {
            return [
                'type' => self::BASE_TYPE_URI . '/http-error',
                'title' => Response::$statusTexts[$exception->getStatusCode()] ?? 'Error',
                'status' => $exception->getStatusCode(),
                'detail' => $exception->getMessage(),
                'instance' => $path,
            ];
        }

        // Generic server error
        return [
            'type' => self::BASE_TYPE_URI . '/internal-error',
            'title' => 'Internal Server Error',
            'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
            'detail' => $this->environment === 'prod'
                ? 'An unexpected error occurred.'
                : $exception->getMessage(),
            'instance' => $path,
        ];
    }
}
