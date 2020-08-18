<?php

namespace Core\Http\Middleware;

use Core\Http\Message\RouterStatus;
use Core\Http\Service\RouterProvider;
use Core\Template\TemplateHandler;
use HttpStatusCodes\HttpStatusCodes as StatusCode;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;

class StreamHandler implements MiddlewareInterface
{
    private $service;
    private $container;

    public function __construct(RouterProvider $service, ContainerInterface $container)
    {
        $this->service = $service;
        $this->container = $container;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var RouterStatus $data */
        $data = $request->getAttribute(RouterHandler::class);

        if (!$data) {
            $classname = RouterHandler::class;
            throw new InvalidArgumentException("Attribute request with '{$classname}' not found.");
        }

        if ($data->getStatus() !== StatusCode::HTTP_OK_CODE) {
            return $handler->handle($request);
        }

        $templateHandler = $this->container->get(TemplateHandler::class);
        
        $this->service->addMiddleware($templateHandler);
        return $this->service->run($request, $handler);
    }
}
