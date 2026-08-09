<?php

namespace App\Application\Transaction\DTO;

class TransactionListView
{
    /**
     * @param TransactionView[] $transactions
     */
    public function __construct(
        public array $transactions = [],
        public int $total = 0,
    ) {}
}
