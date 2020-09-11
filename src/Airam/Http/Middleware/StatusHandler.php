<?php

namespace Airam\Http\Middleware;

use Airam\Http\Service\RouteService;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class StatusHandler implements MiddlewareInterface
{
    private $service;
    public function __construct(RouteService $service)
    {
        $this->service = $service;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $with = $this->service;
        $with($request);

        return $handler->handle($request);
    }
}
