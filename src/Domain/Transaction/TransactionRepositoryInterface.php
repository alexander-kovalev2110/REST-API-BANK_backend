<?php

namespace App\Domain\Transaction;

use App\Domain\Customer\Customer;

interface TransactionRepositoryInterface
{
    public function save(Transaction $transaction): void;

    public function remove(Transaction $transaction): void;

    public function findByIdAndCustomer(int $id, Customer $customer): ?Transaction;
}
