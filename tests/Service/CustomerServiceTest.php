<?php

namespace App\Tests\Service;

use App\Domain\Customer\Customer;
use App\Domain\Customer\CustomerRepositoryInterface;
use App\Domain\Customer\Exception\CustomerAlreadyExistsException;
use App\Domain\Customer\Exception\CustomerNotFoundException;
use App\Domain\Customer\Exception\InvalidCredentialsException;
use App\Application\Customer\Service\PasswordHasherInterface;
use App\Application\Customer\Command\RegisterCustomerCommand;
use App\Application\Customer\Command\RegisterCustomerHandler;
use App\Application\Customer\Command\LoginCustomerCommand;
use App\Application\Customer\Command\LoginCustomerHandler;
use PHPUnit\Framework\TestCase;

class CustomerServiceTest extends TestCase
{
    private CustomerRepositoryInterface $customerRepo;
    private PasswordHasherInterface $passwordHasher;
    private RegisterCustomerHandler $registerHandler;
    private LoginCustomerHandler $loginHandler;

    protected function setUp(): void
    {
        $this->customerRepo = $this->createMock(CustomerRepositoryInterface::class);
        $this->passwordHasher = $this->createMock(PasswordHasherInterface::class);

        $this->registerHandler = new RegisterCustomerHandler(
            $this->customerRepo,
            $this->passwordHasher
        );

        $this->loginHandler = new LoginCustomerHandler(
            $this->customerRepo,
            $this->passwordHasher
        );
    }

    public function testCreateCustomerSuccess(): void
    {
        $command = new RegisterCustomerCommand('alex_k', 'password');

        $this->customerRepo->expects($this->once())
            ->method('findByName')
            ->with('alex_k')
            ->willReturn(null);

        $this->passwordHasher->expects($this->once())
            ->method('hash')
            ->willReturn('hashed_password');

        $this->customerRepo->expects($this->once())
            ->method('save');

        $customer = ($this->registerHandler)($command);

        $this->assertInstanceOf(Customer::class, $customer);
        $this->assertEquals('alex_k', $customer->getName());
        $this->assertEquals('hashed_password', $customer->getPassword());
    }

    public function testCreateCustomerAlreadyExists(): void
    {
        $command = new RegisterCustomerCommand('existing_user', 'password');
        $existingCustomer = new Customer();

        $this->customerRepo->expects($this->once())
            ->method('findByName')
            ->with('existing_user')
            ->willReturn($existingCustomer);

        $this->expectException(CustomerAlreadyExistsException::class);

        ($this->registerHandler)($command);
    }

    public function testLoginSuccess(): void
    {
        $command = new LoginCustomerCommand('alex_k', 'password');
        $customer = new Customer();
        $customer->setName('alex_k');

        $this->customerRepo->expects($this->once())
            ->method('findByName')
            ->with('alex_k')
            ->willReturn($customer);

        $this->passwordHasher->expects($this->once())
            ->method('isValid')
            ->with($customer, 'password')
            ->willReturn(true);

        $result = ($this->loginHandler)($command);

        $this->assertSame($customer, $result);
    }

    public function testLoginCustomerNotFound(): void
    {
        $command = new LoginCustomerCommand('non_existent', 'password123');

        $this->customerRepo->expects($this->once())
            ->method('findByName')
            ->with('non_existent')
            ->willReturn(null);

        $this->expectException(CustomerNotFoundException::class);

        ($this->loginHandler)($command);
    }

    public function testLoginInvalidCredentials(): void
    {
        $command = new LoginCustomerCommand('alex_k', 'wrong_password');
        $customer = new Customer();

        $this->customerRepo->expects($this->once())
            ->method('findByName')
            ->with('alex_k')
            ->willReturn($customer);

        $this->passwordHasher->expects($this->once())
            ->method('isValid')
            ->with($customer, 'wrong_password')
            ->willReturn(false);

        $this->expectException(InvalidCredentialsException::class);

        ($this->loginHandler)($command);
    }
}
