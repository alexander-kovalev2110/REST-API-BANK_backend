<?php

namespace App\Application\Customer\Command;

final class LoginCustomerCommand
{
    public function __construct(
        public readonly string $name,
        public readonly string $password
    ) {}
}
