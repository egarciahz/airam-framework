<?php

namespace Core\Http\Middleware;

use Fig\Http\Message\StatusCodeInterface as StatusCode;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ErrorHandler implements MiddlewareInterface
{
    /** @var callable $responseFactory */
    private $responseFactory;

    public function __construct(callable $responseFactory, bool $isDevelopmentMode = false)
    {
        $this->responseFactory = function () use ($responseFactory): ResponseInterface {
            return $responseFactory();
        };
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var ResponseInterface $response */
        $response = ($this->responseFactory)();

        //$response->withStatus($router->status);

        $response->getBody()->write(sprintf(
            'Http error %s %s',
            $request->getMethod(),
            (string) $request->getUri()
        ));

        return $response;
    }
}
