<?php

namespace App\Application\Transaction\Command;

use App\Domain\Transaction\TransactionRepositoryInterface;
use App\Domain\Transaction\Exception\TransactionNotFoundException;
use App\Application\Transaction\DTO\TransactionListView;
use App\Application\Transaction\DTO\TransactionView;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class UpdateTransactionHandler
{
    public function __construct(
        private TransactionRepositoryInterface $transactionRepo
    ) {}

    public function __invoke(UpdateTransactionCommand $command): TransactionListView
    {
        $transaction = $this->transactionRepo->findByIdAndCustomer(
            $command->transactionId,
            $command->customer
        );

        if (!$transaction) {
            throw new TransactionNotFoundException();
        }

        $transaction->setAmount($command->amount);
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
