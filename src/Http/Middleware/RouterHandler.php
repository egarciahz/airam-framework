<?php

namespace Airam\Http\Middleware;

use Airam\Application;
use Airam\Http\Message\RouterStatus;
use Airam\Http\Message\RouterStatusInterface;
use Laminas\Uri\Uri;
use FastRoute\Dispatcher;
use HttpStatusCodes\HttpStatusCodes as StatusCode;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;


class RouterHandler implements MiddlewareInterface
{
    /** @var Dispatcher $dispatcher */
    private $dispatcher;
    private $app;

    public function __construct(Dispatcher $dispatcher, Application $app)
    {
        $this->dispatcher = $dispatcher;
        $this->app = $app;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {

        if (!$this->app->isProdMode()) {
            $routerModule = $this->app->get("AppMainRouterModule");
            $routerModule->register();
        }
        
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
