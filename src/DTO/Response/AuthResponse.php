<?php

namespace App\DTO\Response;

class AuthResponse
{
    public function __construct(
        public ?string $token = null
    ) {}
}
