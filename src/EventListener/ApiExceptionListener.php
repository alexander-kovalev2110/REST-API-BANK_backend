<?php

namespace App\EventListener;

use App\Domain\Exception\DomainException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

final class ApiExceptionListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        // Business exceptions only
        if ($exception instanceof DomainException) {
            $event->setResponse(
                new JsonResponse(
                    ['error' => $exception->getMessage()],
                    $exception->getStatusCode()
                )
            );
        }
        // All other exceptions are handled by Symfony.
    }
}
