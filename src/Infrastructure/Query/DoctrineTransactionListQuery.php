<?php

namespace App\Infrastructure\Query;

use App\Domain\Transaction\Transaction;
use App\Application\Transaction\Query\GetTransactionListQuery;
use App\Application\Transaction\Service\TransactionListQueryInterface;
use App\Application\Transaction\DTO\TransactionView;
use App\Application\Transaction\DTO\TransactionListView;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineTransactionListQuery implements TransactionListQueryInterface
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function getList(GetTransactionListQuery $query): TransactionListView
    {
        $qb = $this->em->createQueryBuilder()
            ->select('t')
            ->from(Transaction::class, 't')
            ->where('t.customer = :customer')
            ->setParameter('customer', $query->customer);

        if ($query->amount !== null) {
            $qb->andWhere('t.amount = :amount')->setParameter('amount', $query->amount);
        }

        if ($query->date !== null) {
            $start = $query->date->setTime(0, 0, 0);
            $end   = $query->date->setTime(23, 59, 59);
            $qb->andWhere('t.date BETWEEN :start AND :end')
               ->setParameter('start', $start)
               ->setParameter('end', $end);
        }

        $countQb = clone $qb;
        $total = (int) $countQb->select('COUNT(t.id)')->getQuery()->getSingleScalarResult();

        $offset = ($query->page - 1) * $query->limit;
        $qb->setFirstResult($offset)
           ->setMaxResults($query->limit)
           ->orderBy('t.date', 'ASC');

        $transactions = $qb->getQuery()->getResult();

        $views = array_map(function (Transaction $t) {
            return new TransactionView(
                $t->getId(),
                $t->getAmount(),
                $t->getDate()->format('Y-m-d'),
                $t->getCustomer()->getId()
            );
        }, $transactions);

        return new TransactionListView($views, $total);
    }
}
