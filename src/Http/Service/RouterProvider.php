<?php

namespace Core\Http\Service;

use Core\Application;
use Core\Http\Router;
use Core\RequireException;
use Laminas\Stratigility\MiddlewarePipe;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;

use Throwable;
use Exception;

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

    public function app()
    {
        return $this->app;
    }

    public function router(): Router
    {
        return $this->router;
    }

    public function register(string $path)
    {
        if (!$this->app->isProdMode() && file_exists($path)) {
            $file = file_get_contents($path);
            $name = basename($path);
            $file = str_replace("<?php", "
/** 
 * @name {$name} 
 * @file {$path}
 */
            ", $file);
            $file = str_replace("?>", "/** EOL **/", $file);
            try {
                eval($file);
            } catch (Exception $error) {
                throw new RequireException("A ocurrido un error mientras se reguistraba una ruta de archivo.", $path, $error);
            }
        }
    }

    public function pushMiddleware(MiddlewareInterface $middleware)
    {
        $this->stream->pipe($middleware);
    }

    public function run(ServerRequestInterface $request)
    {
        return $this->stream->handle($request);
    }
}
