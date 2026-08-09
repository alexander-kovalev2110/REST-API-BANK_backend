<?php

namespace App\Application\Customer\Command;

use App\Domain\Customer\Customer;
use App\Domain\Customer\CustomerRepositoryInterface;
use App\Domain\Customer\Exception\CustomerNotFoundException;
use App\Domain\Customer\Exception\InvalidCredentialsException;
use App\Application\Customer\Service\PasswordHasherInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class LoginCustomerHandler
{
    public function __construct(
        private CustomerRepositoryInterface $customerRepo,
        private PasswordHasherInterface $passwordHasher
    ) {}

    public function __invoke(LoginCustomerCommand $command): Customer
    {
        $customer = $this->customerRepo->findByName($command->name);

        if (!$customer) {
            throw new CustomerNotFoundException();
        }

        if (!$this->passwordHasher->isValid($customer, $command->password)) {
            throw new InvalidCredentialsException();
        }

        return $customer;
    }
}
