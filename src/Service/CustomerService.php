<?php

namespace App\Service;

use App\Entity\Customer;
use App\DTO\RegisterRequest;
use App\DTO\LoginRequest;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Domain\Exception\CustomerAlreadyExistsException;
use App\Domain\Exception\CustomerNotFoundException;
use App\Domain\Exception\InvalidCredentialsException;

class CustomerService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly CustomerRepository $customerRepo,
    ) {}

    // REGESTERING A NEW CLIENT
    public function create(RegisterRequest $dto): Customer
    {
        // Check for existence
        if ($this->customerRepo->findOneBy(['name' => $dto->name])) {
            throw new CustomerAlreadyExistsException();
        }

        $customer = new Customer();
        $customer->setName($dto->name);

        $customer->setPassword(
            $this->passwordHasher->hashPassword($customer, $dto->password)
        );

        $this->em->persist($customer);
        $this->em->flush();

        return $customer;
    }
 
    // CLIENT AUTHENTICATION
    public function login(LoginRequest $dto): Customer
    {
        $customer = $this->customerRepo->findOneBy(['name' => $dto->name]);

        if (!$customer) {
            throw new CustomerNotFoundException();
        }

        if (!$this->passwordHasher->isPasswordValid($customer, $dto->password)) {
            throw new InvalidCredentialsException();
        }

        return $customer;
    }
}
