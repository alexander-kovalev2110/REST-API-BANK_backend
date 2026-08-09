<?php

namespace App\Application\Transaction\Command;

use App\Domain\Customer\Customer;

final class CreateTransactionCommand
{
    public function __construct(
        public readonly Customer $customer,
        public readonly float $amount
    ) {}
}
