<?php

namespace Airam\Http\Lib;

use Laminas\Uri\UriInterface;
use Psr\Http\Message\ServerRequestInterface;

class Protocol
{
    private $request;

    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
    }

    public function name(): string
    {
        return $this->secure() ? "HTTPS" : "HTTP";
    }

    public function version(): string
    {
        return $this->request->getProtocolVersion();
    }

    public function secure(): bool
    {
        return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== "off");
    }

    public function __toString()
    {
        return $this->name() . " " . $this->version();
    }
}
