<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class AmountTransactionRequest
{
    #[Assert\NotBlank(message: 'Amount is required.')]
    #[Assert\Type(type: 'numeric', message: 'Amount must be a numeric.')]
    #[Assert\Positive(message: 'Amount must be greater than zero.')]
    public ?float $amount = null;
}
