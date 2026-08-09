<?php

namespace App\Application\Transaction\Command;

use App\Domain\Customer\Customer;

final class UpdateTransactionCommand
{
    public function __construct(
        public readonly Customer $customer,
        public readonly int $transactionId,
        public readonly float $amount
    ) {}
}
