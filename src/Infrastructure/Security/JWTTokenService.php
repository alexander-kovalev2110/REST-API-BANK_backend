<?php

namespace App\Infrastructure\Security;

use App\Domain\Customer\Customer;
use App\Application\Customer\Service\TokenServiceInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

final class JWTTokenService implements TokenServiceInterface
{
    public function __construct(
        private JWTTokenManagerInterface $jwt
    ) {}

    public function createToken(Customer $customer): string
    {
        return $this->jwt->createFromPayload($customer, []);
    }
}
