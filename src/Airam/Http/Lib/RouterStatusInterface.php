<?php

namespace Airam\Http\Lib;

use Laminas\Uri\UriInterface;

interface RouterStatusInterface
{
    /**
     * @return string|array|callable
     */
    public function getHandler();
    public function getParams(): array;
    public function getStatus(): int;
    public function getUri(): UriInterface;
}
