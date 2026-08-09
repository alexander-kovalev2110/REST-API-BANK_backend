<?php

namespace App\Application\Customer\Command;

use App\Domain\Customer\Customer;
use App\Domain\Customer\CustomerRepositoryInterface;
use App\Domain\Customer\Exception\CustomerAlreadyExistsException;
use App\Application\Customer\Service\PasswordHasherInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class RegisterCustomerHandler
{
    public function __construct(
        private CustomerRepositoryInterface $customerRepo,
        private PasswordHasherInterface $passwordHasher
    ) {}

    public function __invoke(RegisterCustomerCommand $command): Customer
    {
        if ($this->customerRepo->findByName($command->name)) {
            throw new CustomerAlreadyExistsException();
        }

        $customer = new Customer();
        $customer->setName($command->name);
        $customer->setPassword(
            $this->passwordHasher->hash($customer, $command->password)
        );

        $this->customerRepo->save($customer);

        return $customer;
    }
}
