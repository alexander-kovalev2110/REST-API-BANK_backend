<?php

namespace App\Service;

use App\Entity\Transaction;
use App\DTO\TransactionResponse;

final class TransactionMapper
{
    public static function fromEntity(Transaction $transaction): TransactionResponse
    {
        return new TransactionResponse(
            transactionId: $transaction->getId(),
            amount: $transaction->getAmount(),
            date: $transaction->getDate()->format('Y-m-d'),
            customerId: $transaction->getCustomer()->getId()
        );
    }

    /**
     * @param Transaction[] $transactions
     * @return TransactionResponse[]
     */
    public static function fromEntities(iterable $transactions): array
    {
        return array_map(
            self::fromEntity(...),
            $transactions
        );
    }
}
