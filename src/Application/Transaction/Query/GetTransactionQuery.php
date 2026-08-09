<?php

namespace App\Application\Transaction\Query;

use App\Domain\Customer\Customer;

final class GetTransactionQuery
{
    public function __construct(
        public readonly Customer $customer,
        public readonly int $transactionId
    ) {}
}
