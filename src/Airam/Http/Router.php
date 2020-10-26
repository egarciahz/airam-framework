<?php

namespace Airam\Http;

use Airam\Compiler\{Compiler, Compilable};
use Airam\Http\Lib\RouterSplInterface;
use FastRoute\{DataGenerator, RouteCollector, Dispatcher, RouteParser};
use FastRoute\Dispatcher\GroupCountBased;
use Psr\Container\ContainerInterface;
use RuntimeException;

use function Airam\Commons\{loadResource, path_join};

class Router extends RouteCollector implements Dispatcher, Compilable
{
    const HANDLE_STATUS_CODE = "0x_RouterStatusData.Code$";
    const HANDLE_MODULE_CODE = "1x_RouterModuleRef.Code$";

    private $app;

    private $atCompilationIsEnabled = false;
    private $filename = null;
    private $isDevMode = true;
    private $cache = null;

    private static $instance;

    public function __construct(RouteParser $parser, DataGenerator $generator, ContainerInterface $app)
    {
        parent::__construct($parser, $generator);
        $this->app = $app;

        static::$instance = $this;
    }

    public static function getInstance()
    {
        return static::$instance;
    }

    public function getData()
    {
        if ($this->isDevMode) {
            return parent::getData();
        }

        $this->cache = loadResource($this->filename);

        if ($this->cache === null) {
            throw new \RuntimeException("Not found cache file {$this->filename}, please call build method after dispatch");
        }

        if ($this->cache === false) {
            throw new \RuntimeException("Unreadable cache file {$this->filename}");
        }

        if (!is_array($this->cache)) {
            throw new \RuntimeException("Invalid cache file {$this->filename}");
        }

        return $this->cache;
    }

    public function enableCompilation(?string $path, bool $at): self
    {
        if (!file_exists($path)) {
            throw new \RuntimeException("Invalid cache folder {$path}");
        }

        $this->atCompilationIsEnabled = $at;
        $this->filename = path_join(DIRECTORY_SEPARATOR, $path, "RouterContainer.php");
        $this->isDevMode = false;

        return $this;
    }

    public function dispatch($httpMethod, $uri)
    {
        $data = $this->getData();
        $base = new GroupCountBased($data);
        return $base->dispatch($httpMethod, $uri);
    }

    public function build(): void
    {
        if ($this->atCompilationIsEnabled ?: !$this->isDevMode && !file_exists($this->filename)) {
            $this->loadRouterFiles();
            $data = parent::getData();
            Compiler::bundle($data, $this->filename, AIRAM_CACHE_NAMESPACE);
        }
    }

    private function loadRouterFiles()
    {
        if ($this->app->has(Router::HANDLE_MODULE_CODE)) {
            /** @var RouterSplInterface $routerModule */
            $routerModule = $this->app->get(Router::HANDLE_MODULE_CODE);
            $paths = $routerModule->register();
            foreach ($paths as $path) {
                if ($rpath = realpath($path)) {
                    loadResource($rpath);
                    continue;
                }

                throw new RuntimeException("Could not be found file: {$path}");
            }
        } else {
            throw new RuntimeException("could not be found RouterModule: Please add a RouterModule to the application.");
        }
    }
}
