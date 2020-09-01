<?php

namespace Airam\Service;

use Airam\Application;
use Airam\RequireException;
use Laminas\Stratigility\MiddlewarePipe;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;

use Exception;

/**
 * @Injectable()
 */
class ApplicationService
{
    private $app;
    private $stream;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->stream = new MiddlewarePipe;
    }

    public function app()
    {
        return $this->app;
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
            $file = str_replace("?>", "/** EOF **/", $file);
            try {
                eval($file);
            } catch (Exception $error) {
                throw new RequireException("unexpected error ocurred while eval file", $path, $error);
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
