<?php

namespace Core\Http\Service;

use Core\Application;
use Core\Http\Router;
use Laminas\Stratigility\MiddlewarePipe;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;

/**
 * @Injectable()
 */
class RouterProvider
{
    private $app;
    private $router;
    private $stream;

    public function __construct(Application $app, Router $router)
    {
        $this->app = $app;
        $this->router = $router;
        $this->stream = new MiddlewarePipe;
    }

    public function router(): Router
    {
        return $this->router;
    }

    public function register(string $path)
    {
        if (!$this->app->isProdMode()) {
            require $path;
        }
    }

    public function addMiddleware(MiddlewareInterface $middleware)
    {
        $this->stream->pipe($middleware);
    }

    public function run(ServerRequestInterface $request)
    {
        return $this->stream->handle($request);
    }
}
