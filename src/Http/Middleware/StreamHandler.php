<?php

namespace Core\Http\Middleware;

use Core\Http\Message\RouterStatus;
use Core\Http\Message\RouterStatusInterface;
use Core\Http\Service\RouterProvider;
use HttpStatusCodes\HttpStatusCodes as StatusCode;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Container\ContainerInterface;

use InvalidArgumentException;

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
        $data = $request->getAttribute(RouterStatusInterface::class);

        if (!$data) {
            $classname = StreamHandler::class;
            throw new InvalidArgumentException("Attribute request with '{$classname}' not found.");
        }

        if ($data->getStatus() !== StatusCode::HTTP_OK_CODE) {
            return $handler->handle($request);
        }

        return $this->service->run($request, $handler);
    }
}
