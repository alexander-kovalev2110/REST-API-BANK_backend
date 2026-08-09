<?php

namespace App\Application\Transaction\Command;

use App\Domain\Transaction\Transaction;
use App\Domain\Transaction\TransactionRepositoryInterface;
use App\Application\Transaction\DTO\TransactionListView;
use App\Application\Transaction\DTO\TransactionView;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class CreateTransactionHandler
{
    public function __construct(
        private TransactionRepositoryInterface $transactionRepo
    ) {}

    public function __invoke(CreateTransactionCommand $command): TransactionListView
    {
        $transaction = new Transaction();
        $transaction->setCustomer($command->customer);
        $transaction->setAmount($command->amount);
        $transaction->setDate(new \DateTimeImmutable());

        $this->transactionRepo->save($transaction);

        return new TransactionListView(
            transactions: [
                new TransactionView(
                    $transaction->getId(),
                    $transaction->getAmount(),
                    $transaction->getDate()->format(\DateTimeInterface::ATOM),
                    $transaction->getCustomer()->getId()
                )
            ],
            total: 1
        );
    }
}
