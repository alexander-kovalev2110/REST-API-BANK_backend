<?php

namespace App\EventListener;

use App\Domain\Exception\DomainException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Validator\Exception\ValidationFailedException;

final class ApiExceptionListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        // Unwrap Symfony Messenger exceptions from CommandBus / QueryBus
        while ($exception instanceof HandlerFailedException) {
            $nested = method_exists($exception, 'getNestedExceptions') ? $exception->getNestedExceptions() : [];
            $previous = $nested[0] ?? $exception->getPrevious();
            if ($previous === null) {
                break;
            }
            $exception = $previous;
        }

        // 1. Business exceptions (DomainException: 401, 404, 409 etc.)
        if ($exception instanceof DomainException) {
            $event->setResponse(
                new JsonResponse(
                    ['error' => $exception->getMessage()],
                    $exception->getStatusCode()
                )
            );
            return;
        }

        // 2. Native validation exceptions from #[MapRequestPayload] / #[MapQueryString]
        $validationException = null;
        if ($exception instanceof ValidationFailedException) {
            $validationException = $exception;
        } elseif ($exception instanceof HttpExceptionInterface && 
            $exception->getPrevious() instanceof ValidationFailedException) {
            $validationException = $exception->getPrevious();
        }

        if ($validationException !== null) {
            $violations = $validationException->getViolations();
            $errorMessage = $violations->count() > 0 ? $violations[0]->getMessage() : 'Validation failed.';

            $event->setResponse(
                new JsonResponse(
                    ['error' => $errorMessage],
                    400
                )
            );
            return;
        }

        // 3. HTTP exceptions (e.g. 404 Not Found, 403 Forbidden, 405 Method Not Allowed)
        if ($exception instanceof HttpExceptionInterface) {
            $event->setResponse(
                new JsonResponse(
                    ['error' => $exception->getMessage() ?: 'Http Error'],
                    $exception->getStatusCode()
                )
            );
            return;
        }

        // 4. All other unhandled exceptions (500)
        $event->setResponse(
            new JsonResponse(
                ['error' => 'Internal Server Error'],
                500
            )
        );
    }
}
