<?php

namespace App\Domain\Exception;

final class InvalidCredentialsException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Invalid credentials.');
    }

    public function getStatusCode(): int
    {
        return 401; // Unauthorized
    }
}
