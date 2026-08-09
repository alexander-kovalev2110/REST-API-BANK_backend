<?php

namespace App\Infrastructure\Bus;

use Symfony\Component\Messenger\MessageBusInterface;
use Psr\Log\LoggerInterface;

abstract readonly class AbstractBus
{
    public function __construct(
        protected MessageBusInterface $bus,
        protected LoggerInterface $logger,
    ) {}

    protected function log(object $message, float $durationMs): void
    {
        $name = (new \ReflectionClass($message))
            ->getShortName();

        $this->logger->info(
            sprintf('%s - %sms', $name, $durationMs)
        );
    }
}
