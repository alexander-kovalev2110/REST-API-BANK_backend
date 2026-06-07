<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class LoginRequest
{
    #[Assert\NotBlank(message: 'Name is required.')]
    public ?string $name = null;

    #[Assert\NotBlank(message: 'Password is required.')]
    public ?string $password = null;
}
