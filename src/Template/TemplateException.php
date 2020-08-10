<?php

namespace Core\Template;

use Exception;
use Throwable;

class TemplateException extends Exception
{
    public function __construct(string $message, string $templateName, string $realpath = null)
    {
        $this->message  = $message;
        $this->template = $templateName;
    }
}
