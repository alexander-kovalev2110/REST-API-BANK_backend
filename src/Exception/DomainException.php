<?php

namespace App\Exception;

use RuntimeException;

abstract class DomainException extends \RuntimeException
{
// Required method - returns HTTP status  
abstract public function getStatusCode(): int;
}
