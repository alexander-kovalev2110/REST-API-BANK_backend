<?php

namespace App\Domain\Customer\Exception;

use App\Domain\Exception\DomainException;

final class InvalidCredentialsException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Invalid credentials.');
    }

    public function getStatusCode(): int
    {
        return 401;
    }
}
