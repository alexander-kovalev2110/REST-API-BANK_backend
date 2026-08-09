<?php

namespace App\Application\Transaction\Query;

use App\Application\Transaction\Service\TransactionQueryInterface;
use App\Application\Transaction\DTO\TransactionListView;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GetTransactionHandler
{
    public function __construct(
        private TransactionQueryInterface $queryService
    ) {}

    public function __invoke(GetTransactionQuery $query): TransactionListView
    {
        return $this->queryService->getTransaction($query->customer, $query->transactionId);
    }
}
