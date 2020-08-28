<?php

namespace Airam\Http\Middleware;

use Airam\Http\Message\RouterStatus;
use Airam\Http\Message\RouterStatusInterface;
use Airam\Http\Service\RouterProvider;
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

    public function __construct(RouterProvider $service)
    {
        $this->service = $service;
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
