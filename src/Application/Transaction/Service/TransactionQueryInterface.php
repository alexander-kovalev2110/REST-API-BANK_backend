<?php

namespace App\Application\Transaction\Service;

use App\Domain\Customer\Customer;
use App\Application\Transaction\DTO\TransactionListView;

interface TransactionQueryInterface
{
    public function getTransaction(Customer $customer, int $transactionId): TransactionListView;
}
