<?php

namespace Airam\Service;

use Airam\Commons\ApplicationService as ApplicationServiceInterface;
use Laminas\Stratigility\MiddlewarePipe;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

class ApplicationService implements ApplicationServiceInterface
{
    private $app;
    private $pipeline;

    public function __construct(ContainerInterface $app)
    {
        $this->app = $app;
        $this->pipeline = new MiddlewarePipe;
    }

    public function app(): ContainerInterface
    {
        return $this->app;
    }

    public function pushMiddleware(MiddlewareInterface $middleware): void
    {
        $this->pipeline->pipe($middleware);
    }

    public function run(ServerRequestInterface $request): ResponseInterface
    {
        return $this->pipeline->handle($request);
    }
}
