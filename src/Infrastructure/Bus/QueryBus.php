<?php

namespace App\Infrastructure\Bus;

use Symfony\Component\Messenger\Stamp\HandledStamp;

final readonly class QueryBus extends AbstractBus
{
    public function ask(object $query): mixed
    {
        $start = microtime(true);

        $envelope = $this->bus->dispatch($query);

        $this->log(
            $query,
            round((microtime(true) - $start) * 1000, 2)
        );

        return $envelope
            ->last(HandledStamp::class)
            ?->getResult();
    }
}
