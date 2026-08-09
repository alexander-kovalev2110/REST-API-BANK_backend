<?php

namespace App\Application\Customer\Service;

use App\Domain\Customer\Customer;

interface PasswordHasherInterface
{
    public function hash(Customer $customer, string $plainPassword): string;

    public function isValid(Customer $customer, string $plainPassword): bool;
}
