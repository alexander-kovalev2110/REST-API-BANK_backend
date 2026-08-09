<?php

namespace App\Application\Transaction\Command;

use App\Domain\Transaction\TransactionRepositoryInterface;
use App\Domain\Transaction\Exception\TransactionNotFoundException;
use App\Application\Transaction\DTO\TransactionListView;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class DeleteTransactionHandler
{
    public function __construct(
        private TransactionRepositoryInterface $transactionRepo
    ) {}

    public function __invoke(DeleteTransactionCommand $command): TransactionListView
    {
        $transaction = $this->transactionRepo->findByIdAndCustomer(
            $command->transactionId,
            $command->customer
        );

        if (!$transaction) {
            throw new TransactionNotFoundException();
        }

        $this->transactionRepo->remove($transaction);

        return new TransactionListView(
            transactions: [],
            total: 0
        );
    }
}
