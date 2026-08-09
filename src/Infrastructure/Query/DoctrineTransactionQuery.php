<?php

namespace App\Infrastructure\Query;

use App\Domain\Customer\Customer;
use App\Domain\Transaction\Transaction;
use App\Domain\Transaction\Exception\TransactionNotFoundException;
use App\Application\Transaction\Service\TransactionQueryInterface;
use App\Application\Transaction\DTO\TransactionView;
use App\Application\Transaction\DTO\TransactionListView;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineTransactionQuery implements TransactionQueryInterface
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function getTransaction(Customer $customer, int $transactionId): TransactionListView
    {
        $transaction = $this->em->getRepository(Transaction::class)
            ->findOneBy([
                'id' => $transactionId,
                'customer' => $customer
            ]);

        if (!$transaction) {
            throw new TransactionNotFoundException();
        }

        return new TransactionListView(
            transactions: [
                new TransactionView(
                    $transaction->getId(),
                    $transaction->getAmount(),
                    $transaction->getDate()->format('Y-m-d'),
                    $transaction->getCustomer()->getId()
                )
            ],
            total: 1
        );
    }
}
