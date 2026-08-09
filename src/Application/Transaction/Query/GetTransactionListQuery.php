<?php

namespace App\Application\Transaction\Query;

use App\Domain\Customer\Customer;

final class GetTransactionListQuery
{
    public function __construct(
        public readonly Customer $customer,
        public readonly ?float $amount = null,
        public readonly ?\DateTimeImmutable $date = null,
        public readonly int $page = 1,
        public readonly int $limit = 10
    ) {}
}
