<?php

namespace App\Service;

use App\Entity\Customer;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class TokenService
{
    public function __construct(
        private readonly JWTTokenManagerInterface $jwt
    ) {}

    public function createToken(Customer $customer): string
    {
        // Generating a JWT token with user data
        return $this->jwt->createFromPayload($customer, []);
    }
}
