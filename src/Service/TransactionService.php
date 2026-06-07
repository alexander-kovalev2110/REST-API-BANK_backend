<?php
namespace App\Service;

use App\Entity\Customer;
use App\Entity\Transaction;
use App\DTO\TransactionResponse;
use App\DTO\TransactionListResponse;
use App\DTO\FilterTransactionRequest;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\TransactionRepository;
use App\Service\TransactionMapper;
use App\Domain\Exception\TransactionNotFoundException;

class TransactionService 
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TransactionRepository $transactionRepo
    ){}

    // CREATE
    public function createTransaction(Customer $customer, float $amount): TransactionListResponse
    {
        $transaction = (new Transaction())
            ->setCustomer($customer)
            ->setAmount($amount)
            ->setDate(new \DateTimeImmutable());

        $this->em->persist($transaction);
        $this->em->flush();

        return new TransactionListResponse(
            transactions: [TransactionMapper::fromEntity($transaction)],
            total: 1,
        );
    }

    // GET BY ID
    public function getTransaction(Customer $customer, int $transactionId): TransactionListResponse
    {
        $transaction = $this->transactionRepo->findOneBy([
            'customer' => $customer,
            'id' => $transactionId
        ]);

        if (!$transaction) {
            throw new TransactionNotFoundException();
        }

        return new TransactionListResponse(
            transactions: [TransactionMapper::fromEntity($transaction)],
            total: 1,
        );
    }

    // UPDATE
    public function changeAmount(Customer $customer, int $transactionId, float $amount): TransactionListResponse
    {
        $transaction = $this->transactionRepo->findOneBy([
            'customer' => $customer,
            'id' => $transactionId
        ]);

        if (!$transaction) {
            throw new TransactionNotFoundException();
        }

        $transaction->setAmount($amount);
        $this->em->flush();

        return new TransactionListResponse(
            transactions: [TransactionMapper::fromEntity($transaction)],
            total: 1,
        );
    }

    // DELETE
    public function removeTransaction(Customer $customer, int $transactionId): TransactionListResponse
    {
        $transaction = $this->transactionRepo->findOneBy([
            'customer' => $customer,
            'id' => $transactionId
        ]);

        if (!$transaction) {
            throw new TransactionNotFoundException();
        }

        $this->em->remove($transaction);
        $this->em->flush();

        return new TransactionListResponse(
            transactions: [],
            total: 0,
        );
    }

    // GET BY FILTER (PAGINATION)
    public function getTransactionByFilter(Customer $customer, FilterTransactionRequest $filter): TransactionListResponse
    {
        $qb = $this->transactionRepo->createQueryBuilder('t')
            ->where('t.customer = :customer')
            ->setParameter('customer', $customer);

        if ($filter->amount !== null) {
            $qb->andWhere('t.amount = :amount')->setParameter('amount', $filter->amount);
        }

        if ($filter->date !== null) {
            $start = $filter->date->setTime(0,0,0);
            $end   = $filter->date->setTime(23,59,59);
            $qb->andWhere('t.date BETWEEN :start AND :end')
                ->setParameter('start', $start)
                ->setParameter('end', $end);
        }

        $countQb = clone $qb;
        $total = (int) $countQb->select('COUNT(t.id)')->getQuery()->getSingleScalarResult();

        $offset = ($filter->page - 1) * $filter->limit;
        $qb->setFirstResult($offset)
           ->setMaxResults($filter->limit)
           ->orderBy('t.date', 'ASC');

        $transactions = $qb->getQuery()->getResult();

        return new TransactionListResponse(
            transactions: TransactionMapper::fromEntities($transactions),
            total: $total,
        );
    }
}