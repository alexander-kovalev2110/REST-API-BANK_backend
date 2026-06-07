<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final class FilterTransactionRequest
{
    public function __construct(
        public readonly ?\DateTimeImmutable $date,

        #[Assert\Positive]
        public readonly ?float $amount,

        #[Assert\Positive]
        public readonly int $page = 1,

        #[Assert\Positive]
        public readonly int $limit = 10,
    ) {}
}