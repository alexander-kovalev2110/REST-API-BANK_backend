<?php

namespace App\Infrastructure\Repository;

use App\Domain\Customer\Customer;
use App\Domain\Transaction\Transaction;
use App\Domain\Transaction\TransactionRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineTransactionRepository implements TransactionRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function save(Transaction $transaction): void
    {
        $this->em->persist($transaction);
        $this->em->flush();
    }

    public function remove(Transaction $transaction): void
    {
        $this->em->remove($transaction);
        $this->em->flush();
    }

    public function findByIdAndCustomer(int $id, Customer $customer): ?Transaction
    {
        return $this->em->getRepository(Transaction::class)
            ->findOneBy([
                'id' => $id,
                'customer' => $customer
            ]);
    }
}
