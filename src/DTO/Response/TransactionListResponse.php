<?php

namespace App\DTO;

class TransactionListResponse
{
    /**
     * @param TransactionResponse[] $transactions
     * @param int $total
     * @param int $page
     * @param int $limit
     */
    public function __construct(
        public array $transactions = [],
        public int $total = 0,
    ) {}
}