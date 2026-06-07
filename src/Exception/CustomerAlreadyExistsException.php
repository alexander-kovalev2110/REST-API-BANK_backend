<?php

namespace App\Domain\Exception;

final class CustomerAlreadyExistsException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Customer already exists.');
    }

    public function getStatusCode(): int
    {
        return 409; // Conflict
    }
}
