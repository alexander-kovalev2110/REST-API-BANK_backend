<?php

namespace App\Infrastructure\Bus;

use Symfony\Component\Messenger\Stamp\HandledStamp;

final readonly class CommandBus extends AbstractBus
{
    public function dispatch(object $command): mixed
    {
        $start = microtime(true);

        $envelope = $this->bus->dispatch($command);

        $this->log(
            $command,
            round((microtime(true) - $start) * 1000, 2)
        );

        return $envelope
            ->last(HandledStamp::class)
            ?->getResult();
    }
}
