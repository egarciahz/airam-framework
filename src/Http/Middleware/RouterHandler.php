<?php

namespace Core\Http\Middleware;

use Core\Http\Message\RouterStatus;
use Core\Http\Message\RouterStatusInterface;
use Laminas\Uri\Uri;
use FastRoute\Dispatcher;
use HttpStatusCodes\HttpStatusCodes as StatusCode;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;


class RouterHandler implements MiddlewareInterface
{
    /** @var Dispatcher $dispatcher */
    private $dispatcher;

    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = (new Uri((string) $request->getUri()))->getPath();
        $match = $this->dispatcher->dispatch($request->getMethod(), $path);

        switch ($match[0]) {
            case Dispatcher::NOT_FOUND:
                // ... 404 Not Found
                $router = new RouterStatus(StatusCode::HTTP_NOT_FOUND_CODE);

                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                // ... 405 Method Not Allowed
                $allowedMethods = $match[1];
                $router = new RouterStatus(StatusCode::HTTP_METHOD_NOT_ALLOWED_CODE, $allowedMethods);

                break;
            case Dispatcher::FOUND:
                // ... 200 OK FOUND
                $router = new RouterStatus(StatusCode::HTTP_OK_CODE, $match[2], $match[1]);

                break;
            default:
                $router = new RouterStatus(StatusCode::HTTP_IM_A_TEAPOT_CODE);
        }

        $request = $request->withAttribute(RouterStatusInterface::class, $router);
        return $handler->handle($request);
    }
}
