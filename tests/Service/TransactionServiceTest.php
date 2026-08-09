<?php

namespace App\Tests\Service;

use App\Domain\Customer\Customer;
use App\Domain\Transaction\Transaction;
use App\Domain\Transaction\TransactionRepositoryInterface;
use App\Domain\Transaction\Exception\TransactionNotFoundException;
use App\Application\Transaction\Command\CreateTransactionCommand;
use App\Application\Transaction\Command\CreateTransactionHandler;
use App\Application\Transaction\Command\UpdateTransactionCommand;
use App\Application\Transaction\Command\UpdateTransactionHandler;
use App\Application\Transaction\Command\DeleteTransactionCommand;
use App\Application\Transaction\Command\DeleteTransactionHandler;
use App\Application\Transaction\Query\GetTransactionListQuery;
use App\Application\Transaction\DTO\FilterTransactionRequest;
use App\Infrastructure\Query\DoctrineTransactionQuery;
use App\Infrastructure\Query\DoctrineTransactionListQuery;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

class TransactionServiceTest extends TestCase
{
    private TransactionRepositoryInterface $transactionRepo;
    private EntityManagerInterface $em;

    private CreateTransactionHandler $createHandler;
    private UpdateTransactionHandler $updateHandler;
    private DeleteTransactionHandler $deleteHandler;

    private DoctrineTransactionQuery $getTransactionQuery;
    private DoctrineTransactionListQuery $getTransactionListQuery;

    protected function setUp(): void
    {
        $this->transactionRepo = $this->createMock(TransactionRepositoryInterface::class);
        $this->em = $this->createMock(EntityManagerInterface::class);

        $this->createHandler = new CreateTransactionHandler($this->transactionRepo);
        $this->updateHandler = new UpdateTransactionHandler($this->transactionRepo);
        $this->deleteHandler = new DeleteTransactionHandler($this->transactionRepo);

        $this->getTransactionQuery = new DoctrineTransactionQuery($this->em);
        $this->getTransactionListQuery = new DoctrineTransactionListQuery($this->em);
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

        $command = new CreateTransactionCommand($customer, $amount);

        $this->transactionRepo->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Transaction::class))
            ->willReturnCallback(function (Transaction $transaction) {
                $this->setEntityId($transaction, 123);
            });

        $response = ($this->createHandler)($command);

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

        $repositoryMock = $this->createMock(EntityRepository::class);
        $this->em->expects($this->once())
            ->method('getRepository')
            ->with(Transaction::class)
            ->willReturn($repositoryMock);

        $repositoryMock->expects($this->once())
            ->method('findOneBy')
            ->with(['id' => $transactionId, 'customer' => $customer])
            ->willReturn($transaction);

        $response = $this->getTransactionQuery->getTransaction($customer, $transactionId);

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

        $repositoryMock = $this->createMock(EntityRepository::class);
        $this->em->expects($this->once())
            ->method('getRepository')
            ->with(Transaction::class)
            ->willReturn($repositoryMock);

        $repositoryMock->expects($this->once())
            ->method('findOneBy')
            ->willReturn(null);

        $this->expectException(TransactionNotFoundException::class);

        $this->getTransactionQuery->getTransaction($customer, $transactionId);
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

        $command = new UpdateTransactionCommand($customer, $transactionId, $newAmount);

        $this->transactionRepo->expects($this->once())
            ->method('findByIdAndCustomer')
            ->with($transactionId, $customer)
            ->willReturn($transaction);

        $this->transactionRepo->expects($this->once())
            ->method('save')
            ->with($transaction);

        $response = ($this->updateHandler)($command);

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
        $command = new DeleteTransactionCommand($customer, $transactionId);

        $this->transactionRepo->expects($this->once())
            ->method('findByIdAndCustomer')
            ->with($transactionId, $customer)
            ->willReturn($transaction);

        $this->transactionRepo->expects($this->once())
            ->method('remove')
            ->with($transaction);

        $response = ($this->deleteHandler)($command);

        $this->assertEquals(0, $response->total);
        $this->assertEmpty($response->transactions);
    }

    public function testGetTransactionByFilter(): void
    {
        $customer = new Customer();
        $this->setEntityId($customer, 1);

        $query = new GetTransactionListQuery(
            customer: $customer,
            amount: 100.0,
            date: null,
            page: 1,
            limit: 10
        );

        $qb = $this->createMock(QueryBuilder::class);
        $countQb = $this->createMock(QueryBuilder::class);
        $ormQuery = $this->createMock(AbstractQuery::class);
        $countQuery = $this->createMock(AbstractQuery::class);

        $this->em->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($qb);

                // Mock method chain for main QB
        $qb->expects($this->exactly(2))
            ->method('select')
            ->willReturnCallback(function ($arg) use ($qb, $countQb) {
                if ($arg === 't') {
                    return $qb;
                }
                return $countQb;
            });
        $qb->expects($this->once())->method('from')->with(Transaction::class, 't')->willReturnSelf();
        $qb->expects($this->once())->method('where')->with('t.customer = :customer')->willReturnSelf();
        $qb->expects($this->atLeastOnce())->method('setParameter')->willReturnSelf();
        $qb->expects($this->once())->method('andWhere')->with('t.amount = :amount')->willReturnSelf();

        // select is called on $countQb (which is cloned from $qb)
        $countQb->expects($this->once())->method('getQuery')->willReturn($countQuery);
        $countQuery->expects($this->once())->method('getSingleScalarResult')->willReturn(5);

        // For the main query:
        $qb->expects($this->once())->method('setFirstResult')->with(0)->willReturnSelf();
        $qb->expects($this->once())->method('setMaxResults')->with(10)->willReturnSelf();
        $qb->expects($this->once())->method('orderBy')->with('t.date', 'ASC')->willReturnSelf();
        $qb->expects($this->once())->method('getQuery')->willReturn($ormQuery);

        $transaction = new Transaction();
        $this->setEntityId($transaction, 42);
        $transaction->setCustomer($customer);
        $transaction->setAmount(100.0);
        $transaction->setDate(new \DateTimeImmutable());

        $ormQuery->expects($this->once())->method('getResult')->willReturn([$transaction]);

        $response = $this->getTransactionListQuery->getList($query);

        $this->assertEquals(5, $response->total);
        $this->assertCount(1, $response->transactions);
        $this->assertEquals(42, $response->transactions[0]->transactionId);
        $this->assertEquals(1, $response->transactions[0]->customerId);
        $this->assertEquals(100.0, $response->transactions[0]->amount);
    }
}
