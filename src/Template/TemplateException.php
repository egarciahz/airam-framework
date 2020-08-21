<?php

namespace Core\Template;

use Exception;

class TemplateException extends Exception
{
    public function __construct(string $message, int $code, string $realpath)
    {
        $this->code = $code;
        $this->message = $message;
        $this->file = $realpath;
    }
}
