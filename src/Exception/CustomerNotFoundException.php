<?php

namespace App\Domain\Exception;

final class CustomerNotFoundException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Customer not found.');
    }

    public function getStatusCode(): int
    {
        return 404;
    }
}


