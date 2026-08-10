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
            return;
        }

        // Native validation exceptions from #[MapRequestPayload]
        $validationException = null;
        if ($exception instanceof \Symfony\Component\Validator\Exception\ValidationFailedException) {
            $validationException = $exception;
        } elseif ($exception instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface && 
            $exception->getPrevious() instanceof \Symfony\Component\Validator\Exception\ValidationFailedException) {
            $validationException = $exception->getPrevious();
        }

        if ($validationException !== null) {
            $errors = [];
            foreach ($validationException->getViolations() as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }

            $event->setResponse(
                new JsonResponse([
                    'message' => 'Validation failed',
                    'errors' => $errors
                ], 400) // Return 400 to match frontend expectation
            );
        }
        // All other exceptions are handled by Symfony.
    }
}
