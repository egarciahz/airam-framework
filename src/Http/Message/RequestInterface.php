<?php

namespace Core\Http\Message;

use Psr\Http\Message\ServerRequestInterface;
use Core\Http\RouterStatusInterface;

interface RequestInterface extends ServerRequestInterface
{
    public function getRouterStatus(): RouterStatusInterface;
    public function setRouterStatus($status, array $data, string $message = ""): void;
}
