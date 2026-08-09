<?php

namespace App\Domain\Customer\Exception;

use App\Domain\Exception\DomainException;

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
