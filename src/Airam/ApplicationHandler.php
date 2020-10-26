<?php

namespace Airam;

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
        /**
         * 
         */
        return $handler->handle($request);
    }
}
