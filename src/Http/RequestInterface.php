<?php

namespace Core\Http;

use Psr\Http\Message\ServerRequestInterface;

interface RequestInterface extends ServerRequestInterface
{
    public function getRouterStatus(): RouterStatusInterface;
    public function setRouterStatus($status, array $data, string $message = ""): void;
}
