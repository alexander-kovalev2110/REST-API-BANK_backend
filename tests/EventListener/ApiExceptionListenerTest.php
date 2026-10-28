<?php

namespace App\Tests\EventListener;

use App\Domain\Customer\Exception\CustomerAlreadyExistsException;
use App\EventListener\ApiExceptionListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Exception\ValidationFailedException;

class ApiExceptionListenerTest extends TestCase
{
    private ApiExceptionListener $listener;
    private HttpKernelInterface $kernel;

    protected function setUp(): void
    {
        $this->listener = new ApiExceptionListener();
        $this->kernel = $this->createMock(HttpKernelInterface::class);
    }

    private function createEvent(\Throwable $exception): ExceptionEvent
    {
        return new ExceptionEvent(
            $this->kernel,
            new Request(),
            HttpKernelInterface::MAIN_REQUEST,
            $exception
        );
    }

    public function testHandlesDomainException(): void
    {
        $exception = new CustomerAlreadyExistsException();
        $event = $this->createEvent($exception);

        ($this->listener)($event);

        $response = $event->getResponse();
        $this->assertNotNull($response);
        $this->assertSame(409, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode(['error' => 'Customer already exists.']),
            $response->getContent()
        );
    }

    public function testHandlesValidationExceptionReturnsFirstError(): void
    {
        $violations = new ConstraintViolationList([
            new ConstraintViolation('This value should not be blank.', null, [], null, 'name', null),
            new ConstraintViolation('Password is too short.', null, [], null, 'password', null),
        ]);
        $exception = new ValidationFailedException('payload', $violations);
        $event = $this->createEvent($exception);

        ($this->listener)($event);

        $response = $event->getResponse();
        $this->assertNotNull($response);
        $this->assertSame(400, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode(['error' => 'This value should not be blank.']),
            $response->getContent()
        );
    }

    public function testHandlesHttpException(): void
    {
        $exception = new NotFoundHttpException('Route not found');
        $event = $this->createEvent($exception);

        ($this->listener)($event);

        $response = $event->getResponse();
        $this->assertNotNull($response);
        $this->assertSame(404, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode(['error' => 'Route not found']),
            $response->getContent()
        );
    }

    public function testHandlesGenericException(): void
    {
        $exception = new \RuntimeException('Database connection lost');
        $event = $this->createEvent($exception);

        ($this->listener)($event);

        $response = $event->getResponse();
        $this->assertNotNull($response);
        $this->assertSame(500, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode(['error' => 'Internal Server Error']),
            $response->getContent()
        );
    }

    public function testHandlesHandlerFailedExceptionWithDomainException(): void
    {
        $domainException = new \App\Domain\Customer\Exception\CustomerNotFoundException();
        $envelope = new \Symfony\Component\Messenger\Envelope(new \stdClass());
        $exception = new \Symfony\Component\Messenger\Exception\HandlerFailedException($envelope, [$domainException]);

        $event = $this->createEvent($exception);

        ($this->listener)($event);

        $response = $event->getResponse();
        $this->assertNotNull($response);
        $this->assertSame(404, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode(['error' => 'Customer not found.']),
            $response->getContent()
        );
    }
}
