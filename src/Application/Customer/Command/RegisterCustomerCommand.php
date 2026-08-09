<?php

namespace App\Application\Customer\Command;

final class RegisterCustomerCommand
{
    public function __construct(
        public readonly string $name,
        public readonly string $password
    ) {}
}
