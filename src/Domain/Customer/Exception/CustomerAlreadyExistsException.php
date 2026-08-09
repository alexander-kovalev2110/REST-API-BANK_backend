<?php

namespace App\Domain\Customer\Exception;

use App\Domain\Exception\DomainException;

final class CustomerAlreadyExistsException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Customer already exists.');
    }

    public function getStatusCode(): int
    {
        return 409;
    }
}
