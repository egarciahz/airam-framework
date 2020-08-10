<?php

namespace Core\Http;

use FastRoute\Dispatcher;

interface RouterStatusInterface
{
    const FOUND = Dispatcher::FOUND;
    const NOT_FOUND = Dispatcher::NOT_FOUND;
    const METHOD_NOT_ALLOWED = Dispatcher::METHOD_NOT_ALLOWED;

    public function getHandler(): string;
    public function getParams(): array;
}
