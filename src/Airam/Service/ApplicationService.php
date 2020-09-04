<?php

namespace Airam\Service;

use Airam\Commons\ApplicationService as ApplicationServiceInterface;
use Airam\RequireException;
use Laminas\Stratigility\MiddlewarePipe;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;

use Exception;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

class ApplicationService implements ApplicationServiceInterface
{
    private $app;
    private $stream;

    public function __construct(ContainerInterface $app)
    {
        $this->app = $app;
        $this->stream = new MiddlewarePipe;
    }

    public function app(): ContainerInterface
    {
        return $this->app;
    }

    public function register(string $path): void
    {
        if (file_exists($path)) {
            $file = file_get_contents($path);
            $name = basename($path);
            $file = str_replace("<?php", "
/** 
 * @name {$name} 
 * @file {$path}
 */
            ", $file);
            $file = str_replace("?>", "/** EOF **/", $file);
            try {
                eval($file);
            } catch (Exception $error) {
                throw new RequireException("unexpected error ocurred while eval file", $path, $error);
            }
        }
    }

    public function pushMiddleware(MiddlewareInterface $middleware): void
    {
        $this->stream->pipe($middleware);
    }

    public function run(ServerRequestInterface $request): ResponseInterface
    {
        return $this->stream->handle($request);
    }
}
