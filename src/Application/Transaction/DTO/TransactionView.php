<?php

namespace App\Application\Transaction\DTO;

class TransactionView
{
    public function __construct(
        public int $transactionId,
        public float $amount,
        public string $date,
        public int $customerId
    ) {}
}
