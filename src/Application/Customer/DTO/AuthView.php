<?php

namespace App\Application\Customer\DTO;

class AuthView
{
    public function __construct(
        public ?string $token = null
    ) {}
}
