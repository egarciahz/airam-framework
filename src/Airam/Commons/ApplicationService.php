<?php

namespace Airam\Commons;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;

interface ApplicationService
{
    public function __construct(ContainerInterface $app);

    public function app(): ContainerInterface;

    public function pushMiddleware(MiddlewareInterface $middleware): void;

    public function run(ServerRequestInterface $request): ResponseInterface;
}
