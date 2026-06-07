<?php

namespace App\DTO;

class AuthResponse
{
    public function __construct(
        public ?string $token = null
    ) {}
}
