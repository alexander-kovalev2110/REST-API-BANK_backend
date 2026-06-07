<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class RegisterRequest
{
    #[Assert\NotBlank(message: 'Name is required.')]
    #[Assert\Length(min: 3, max: 255, minMessage: 'Name must be at least 3 characters long.')]
    public ?string $name = null;

    #[Assert\NotBlank(message: 'Password is required.')]
    #[Assert\Length(min: 3, max: 255, minMessage: 'Password must be at least 3 characters long.')]
    public ?string $password = null;
}
