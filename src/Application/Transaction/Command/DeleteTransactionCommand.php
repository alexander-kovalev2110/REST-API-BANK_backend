<?php

namespace App\Application\Transaction\Command;

use App\Domain\Customer\Customer;

final class DeleteTransactionCommand
{
    public function __construct(
        public readonly Customer $customer,
        public readonly int $transactionId
    ) {}
}
