<?php

namespace App\Tests\Service;

use App\DTO\Request\LoginRequest;
use App\DTO\Request\RegisterRequest;
use App\Entity\Customer;
use App\Exception\CustomerAlreadyExistsException;
use App\Exception\CustomerNotFoundException;
use App\Exception\InvalidCredentialsException;
use App\Repository\CustomerRepository;
use App\Service\CustomerService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CustomerServiceTest extends TestCase
{
    private EntityManagerInterface $em;
    private UserPasswordHasherInterface $passwordHasher;
    private CustomerRepository $customerRepo;
    private CustomerService $customerService;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->customerRepo = $this->createMock(CustomerRepository::class);

        $this->customerService = new CustomerService(
            $this->em,
            $this->passwordHasher,
            $this->customerRepo
        );
    }

    public function testCreateCustomerSuccess(): void
    {
        $dto = new RegisterRequest();
        $dto->name = 'john_doe';
        $dto->password = 'password123';

        $this->customerRepo->expects($this->once())
            ->method('findOneBy')
            ->with(['name' => 'john_doe'])
            ->willReturn(null);

        $this->passwordHasher->expects($this->once())
            ->method('hashPassword')
            ->willReturn('hashed_password');

        $this->em->expects($this->once())
            ->method('persist');
        $this->em->expects($this->once())
            ->method('flush');

        $customer = $this->customerService->create($dto);

        $this->assertInstanceOf(Customer::class, $customer);
        $this->assertEquals('john_doe', $customer->getName());
        $this->assertEquals('hashed_password', $customer->getPassword());
    }

    public function testCreateCustomerAlreadyExists(): void
    {
        $dto = new RegisterRequest();
        $dto->name = 'existing_user';
        $dto->password = 'password123';

        $existingCustomer = new Customer();

        $this->customerRepo->expects($this->once())
            ->method('findOneBy')
            ->with(['name' => 'existing_user'])
            ->willReturn($existingCustomer);

        $this->expectException(CustomerAlreadyExistsException::class);

        $this->customerService->create($dto);
    }

    public function testLoginSuccess(): void
    {
        $dto = new LoginRequest();
        $dto->name = 'john_doe';
        $dto->password = 'password123';

        $customer = new Customer();
        $customer->setName('john_doe');

        $this->customerRepo->expects($this->once())
            ->method('findOneBy')
            ->with(['name' => 'john_doe'])
            ->willReturn($customer);

        $this->passwordHasher->expects($this->once())
            ->method('isPasswordValid')
            ->with($customer, 'password123')
            ->willReturn(true);

        $result = $this->customerService->login($dto);

        $this->assertSame($customer, $result);
    }

    public function testLoginCustomerNotFound(): void
    {
        $dto = new LoginRequest();
        $dto->name = 'non_existent';
        $dto->password = 'password123';

        $this->customerRepo->expects($this->once())
            ->method('findOneBy')
            ->with(['name' => 'non_existent'])
            ->willReturn(null);

        $this->expectException(CustomerNotFoundException::class);

        $this->customerService->login($dto);
    }

    public function testLoginInvalidCredentials(): void
    {
        $dto = new LoginRequest();
        $dto->name = 'john_doe';
        $dto->password = 'wrong_password';

        $customer = new Customer();

        $this->customerRepo->expects($this->once())
            ->method('findOneBy')
            ->with(['name' => 'john_doe'])
            ->willReturn($customer);

        $this->passwordHasher->expects($this->once())
            ->method('isPasswordValid')
            ->with($customer, 'wrong_password')
            ->willReturn(false);

        $this->expectException(InvalidCredentialsException::class);

        $this->customerService->login($dto);
    }
}
