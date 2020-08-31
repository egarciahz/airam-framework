<?php

namespace Airam\Http\Lib;

use Psr\Http\Message\ServerRequestInterface;

class Match
{
    private $request;
    private $params;
    private $uri;

    public function __construct(ServerRequestInterface $request, RouterStatusInterface $status)
    {
        $this->request = $request;
        $this->uri = $status->getUri();
        $this->params = $status->getParams();
    }

    public function params(): array
    {
        return $this->params;
    }

    public function path(): string
    {
        return $this->uri->getPath();
    }

    public function fragment(): ?string
    {
        return $this->uri->getFragment();
    }

    public function query(): array
    {
        return $this->uri->getQueryAsArray();
    }

    public function method(): string
    {
        return $this->request->getMethod();
    }

    public function protocol(): Protocol
    {
        return new Protocol($this->request);
    }

    /**
     * @return array<string[]>
     */
    public function headers()
    {
        return $this->request->getHeaders();
    }

    public function __toString()
    {
        return $this->request->getMethod() . " " . $this->uri->getPath();
    }
}
