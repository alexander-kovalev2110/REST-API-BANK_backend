<?php

namespace App\Tests\Service;

use App\DTO\Request\FilterTransactionRequest;
use App\Entity\Customer;
use App\Entity\Transaction;
use App\Exception\TransactionNotFoundException;
use App\Repository\TransactionRepository;
use App\Service\TransactionService;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

class TransactionServiceTest extends TestCase
{
    private EntityManagerInterface $em;
    private TransactionRepository $transactionRepo;
    private TransactionService $transactionService;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->transactionRepo = $this->createMock(TransactionRepository::class);

        $this->transactionService = new TransactionService(
            $this->em,
            $this->transactionRepo
        );
    }

    private function setEntityId(object $entity, int $id): void
    {
        $reflection = new \ReflectionClass($entity);
        $property = $reflection->getProperty('id');
        $property->setValue($entity, $id);
    }

    public function testCreateTransaction(): void
    {
        $customer = new Customer();
        $this->setEntityId($customer, 1);
        $amount = 100.50;

        $this->em->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Transaction::class))
            ->willReturnCallback(function (Transaction $transaction) {
                $this->setEntityId($transaction, 123);
            });

        $this->em->expects($this->once())->method('flush');

        $response = $this->transactionService->createTransaction($customer, $amount);

        $this->assertEquals(1, $response->total);
        $this->assertCount(1, $response->transactions);
        $this->assertEquals(123, $response->transactions[0]->transactionId);
        $this->assertEquals(1, $response->transactions[0]->customerId);
        $this->assertEquals($amount, $response->transactions[0]->amount);
    }

    public function testGetTransactionSuccess(): void
    {
        $customer = new Customer();
        $this->setEntityId($customer, 1);
        $transactionId = 42;

        $transaction = new Transaction();
        $this->setEntityId($transaction, $transactionId);
        $transaction->setCustomer($customer);
        $transaction->setAmount(50.0);
        $transaction->setDate(new \DateTimeImmutable());

        $this->transactionRepo->expects($this->once())
            ->method('findOneBy')
            ->with(['customer' => $customer, 'id' => $transactionId])
            ->willReturn($transaction);

        $response = $this->transactionService->getTransaction($customer, $transactionId);

        $this->assertEquals(1, $response->total);
        $this->assertCount(1, $response->transactions);
        $this->assertEquals(42, $response->transactions[0]->transactionId);
        $this->assertEquals(1, $response->transactions[0]->customerId);
        $this->assertEquals(50.0, $response->transactions[0]->amount);
    }

    public function testGetTransactionNotFound(): void
    {
        $customer = new Customer();
        $transactionId = 999;

        $this->transactionRepo->expects($this->once())
            ->method('findOneBy')
            ->willReturn(null);

        $this->expectException(TransactionNotFoundException::class);

        $this->transactionService->getTransaction($customer, $transactionId);
    }

    public function testChangeAmountSuccess(): void
    {
        $customer = new Customer();
        $this->setEntityId($customer, 1);
        $transactionId = 42;
        $newAmount = 75.00;

        $transaction = new Transaction();
        $this->setEntityId($transaction, $transactionId);
        $transaction->setCustomer($customer);
        $transaction->setAmount(50.0);
        $transaction->setDate(new \DateTimeImmutable());

        $this->transactionRepo->expects($this->once())
            ->method('findOneBy')
            ->willReturn($transaction);

        $this->em->expects($this->once())->method('flush');

        $response = $this->transactionService->changeAmount($customer, $transactionId, $newAmount);

        $this->assertEquals(75.00, $transaction->getAmount());
        $this->assertEquals(1, $response->total);
        $this->assertEquals(42, $response->transactions[0]->transactionId);
        $this->assertEquals(1, $response->transactions[0]->customerId);
    }

    public function testRemoveTransactionSuccess(): void
    {
        $customer = new Customer();
        $transactionId = 42;

        $transaction = new Transaction();

        $this->transactionRepo->expects($this->once())
            ->method('findOneBy')
            ->willReturn($transaction);

        $this->em->expects($this->once())->method('remove')->with($transaction);
        $this->em->expects($this->once())->method('flush');

        $response = $this->transactionService->removeTransaction($customer, $transactionId);

        $this->assertEquals(0, $response->total);
        $this->assertEmpty($response->transactions);
    }

    public function testGetTransactionByFilter(): void
    {
        $customer = new Customer();
        $this->setEntityId($customer, 1);
        
        $filter = new FilterTransactionRequest(
            date: null,
            amount: 100.0,
            page: 1,
            limit: 10
        );

        $qb = $this->createMock(QueryBuilder::class);
        $countQb = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(AbstractQuery::class);
        $countQuery = $this->createMock(AbstractQuery::class);

        $this->transactionRepo->expects($this->once())
            ->method('createQueryBuilder')
            ->with('t')
            ->willReturn($qb);

        // Mock method chain for main QB
        $qb->expects($this->once())->method('where')->with('t.customer = :customer')->willReturnSelf();
        $qb->expects($this->atLeastOnce())->method('setParameter')->willReturnSelf();
        $qb->expects($this->once())->method('andWhere')->with('t.amount = :amount')->willReturnSelf();
        
        // select is called on $countQb (which is cloned from $qb)
        $qb->expects($this->once())->method('select')->with('COUNT(t.id)')->willReturn($countQb);
        $countQb->expects($this->once())->method('getQuery')->willReturn($countQuery);
        $countQuery->expects($this->once())->method('getSingleScalarResult')->willReturn(5);

        // For the main query:
        $qb->expects($this->once())->method('setFirstResult')->with(0)->willReturnSelf();
        $qb->expects($this->once())->method('setMaxResults')->with(10)->willReturnSelf();
        $qb->expects($this->once())->method('orderBy')->with('t.date', 'ASC')->willReturnSelf();
        $qb->expects($this->once())->method('getQuery')->willReturn($query);

        $transaction = new Transaction();
        $this->setEntityId($transaction, 42);
        $transaction->setCustomer($customer);
        $transaction->setAmount(100.0);
        $transaction->setDate(new \DateTimeImmutable());

        $query->expects($this->once())->method('getResult')->willReturn([$transaction]);

        $response = $this->transactionService->getTransactionByFilter($customer, $filter);

        $this->assertEquals(5, $response->total);
        $this->assertCount(1, $response->transactions);
        $this->assertEquals(42, $response->transactions[0]->transactionId);
        $this->assertEquals(1, $response->transactions[0]->customerId);
        $this->assertEquals(100.0, $response->transactions[0]->amount);
    }
}
