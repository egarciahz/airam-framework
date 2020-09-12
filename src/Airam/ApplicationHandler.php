<?php

namespace Airam;

use Airam\Http\Lib\RouterSplInterface;
use Airam\Http\Router;
use Airam\Template\Render\Engine as TemplateEngine;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ApplicationHandler implements MiddlewareInterface
{
    private $app;
    public function __construct(ContainerInterface $app)
    {
        $this->app = $app;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var RouterSplInterface $module */
        $module = $this->app->get(Router::HANDLE_MODULE_CODE);
        $module->register();

        !$this->app->has(Router::class) ?: $this->app->get(Router::class)->build();
        !$this->app->has(TemplateEngine::class) ?: $this->app->get(TemplateEngine::class)->build();

        return $handler->handle($request);
    }
}
