<?php

namespace App\Infrastructure\Security;

use App\Domain\Customer\Customer;
use App\Application\Customer\Service\PasswordHasherInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class SymfonyPasswordHasher implements PasswordHasherInterface
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function hash(Customer $customer, string $plainPassword): string
    {
        return $this->passwordHasher->hashPassword($customer, $plainPassword);
    }

    public function isValid(Customer $customer, string $plainPassword): bool
    {
        return $this->passwordHasher->isPasswordValid($customer, $plainPassword);
    }
}
