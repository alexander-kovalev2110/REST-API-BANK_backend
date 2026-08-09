<?php

namespace App\Domain\Transaction\Exception;

use App\Domain\Exception\DomainException;

final class TransactionNotFoundException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Transaction not found.');
    }

    public function getStatusCode(): int
    {
        return 404;
    }
}
