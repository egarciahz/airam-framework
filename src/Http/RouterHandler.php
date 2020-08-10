<?php

namespace Core\Http;

use FastRoute\Dispatcher;

use Fig\Http\Message\StatusCodeInterface as StatusCode;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use stdClass;

class RouterHandler implements MiddlewareInterface
{
    /** @var callable $dispatchFactory */
    private $dispatchFactory;

    /** @var callable $responseFactory */
    private $responseFactory;

    public function __construct(callable $responseFactory, callable $dispatcher)
    {
        $this->dispatchFactory = function () use ($dispatcher): Dispatcher {
            return $dispatcher();
        };

        $this->responseFactory = function () use ($responseFactory): ResponseInterface {
            return $responseFactory();
        };
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var ResponseInterface $response */
        $response = ($this->responseFactory)();

        /** @var Dispatcher $dispatcher */
        $dispatcher = ($this->dispatchFactory)();
        $uri = $_SERVER['REQUEST_URI'];

        // Strip query string (?foo=bar) and decode URI
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);
        $uri = str_replace("/simplext-php-2",'',$uri);
        $match = $dispatcher->dispatch($request->getMethod(), $uri);
        $router = new stdClass();

        switch ($match[0]) {
            case Dispatcher::NOT_FOUND:
                // ... 404 Not Found
                $router->status = StatusCode::STATUS_NOT_FOUND;
                $handler->router =  $router;

                return $handler->handle($request);
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                // ... 405 Method Not Allowed
                $allowedMethods = $match[1];
                $router->status = StatusCode::STATUS_METHOD_NOT_ALLOWED;
                $router->data = $allowedMethods;
                $handler->router =  $router;

                return $handler->handle($request);
                break;
            case Dispatcher::FOUND:
                // ... call $handler with $vars
                $router->status =  StatusCode::STATUS_FOUND;
                $router->data = $match;
                $controller = $match[1];

                $response->getBody()->write("Test: " . $controller );
                break;
        }

        return $response;
    }
}
