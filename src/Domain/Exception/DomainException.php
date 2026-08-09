<?php

namespace App\Domain\Exception;

abstract class DomainException extends \RuntimeException
{
    abstract public function getStatusCode(): int;
}
