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

    public function params()
    {
        return $this->params;
    }

    public function path()
    {
        return $this->uri->getPath();
    }

    public function fragment()
    {
        return $this->uri->getFragment();
    }

    public function query()
    {
        return $this->uri->getQueryAsArray();
    }

    public function method()
    {
        return $this->request->getMethod();
    }

    public function headers()
    {
        return $this->request->getHeaders();
    }
}
