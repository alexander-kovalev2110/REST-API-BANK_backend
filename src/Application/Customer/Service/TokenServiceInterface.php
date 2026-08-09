<?php

namespace App\Application\Customer\Service;

use App\Domain\Customer\Customer;

interface TokenServiceInterface
{
    public function createToken(Customer $customer): string;
}
