<?php

namespace App\Domain\Exception;

final class TransactionNotFoundException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Transaction not found.');
    }

    public function getStatusCode(): int
    {
        return 404; // Not Found
    }
}
