<?php

namespace Airam\Http\Middleware;

use Airam\Http\Router;
use Airam\Http\Message\RouterStatus;
use Airam\Service\ApplicationService;

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
    private $service;

    public function __construct(Dispatcher $dispatcher, ApplicationService $service)
    {
        $this->dispatcher = $dispatcher;
        $this->service = $service;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {

        $routerModule = $this->service->app()->get(Router::HANDLE_MODULE_CODE);
        $routerModule->register();
        
        $uri = new Uri((string) $request->getUri());
        $match = $this->dispatcher->dispatch($request->getMethod(), $uri->getPath());

        switch ($match[0]) {
            case Router::NOT_FOUND:
                // ... 404 Not Found
                $router = new RouterStatus(StatusCode::HTTP_NOT_FOUND_CODE, $uri);

                break;
            case Router::METHOD_NOT_ALLOWED:
                // ... 405 Method Not Allowed
                $allowedMethods = $match[1];
                $router = new RouterStatus(StatusCode::HTTP_METHOD_NOT_ALLOWED_CODE, $uri, $allowedMethods, null);

                break;
            case Router::FOUND:
                // ... 200 OK FOUND
                $router = new RouterStatus(StatusCode::HTTP_OK_CODE, $uri, $match[2], $match[1]);

                break;
            default:
                $router = new RouterStatus(StatusCode::HTTP_IM_A_TEAPOT_CODE, $uri);
        }

        $request = $request->withAttribute(Router::HANDLE_STATUS_CODE, $router);
        return $handler->handle($request);
    }
}
