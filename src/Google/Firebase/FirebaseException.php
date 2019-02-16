<?php

namespace Bedrox\Google\Firebase;

use Exception;
use Throwable;

class FirebaseException extends Exception
{
    public function __construct(string $message = '', int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}