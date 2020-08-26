<?php

namespace Core;

use Exception;
use Throwable;

class RequireException extends Exception
{
    public function __construct(string $message, string $file, Throwable $previeous)
    {
        parent::__construct($message, 500, $previeous);
        $this->file = $file;
        $this->line = $previeous->getLine();
    }
}
