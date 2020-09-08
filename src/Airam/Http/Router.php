<?php

namespace Airam\Http;

use FastRoute\{DataGenerator, RouteCollector, Dispatcher, RouteParser};
use FastRoute\Dispatcher\GroupCountBased;
use function Airam\Commons\{loadResource, path_join};

class Router extends RouteCollector implements Dispatcher
{
    const HANDLE_STATUS_CODE = "0x_RouterStatusData.Code$";
    const HANDLE_MODULE_CODE = "1x_RouterModuleRef.Code$";

    private $filename = null;
    private $isDevMode = true;
    private $cache = null;

    private static $instance;

    public function __construct(RouteParser $parser, DataGenerator $generator)
    {
        parent::__construct($parser, $generator);
        static::$instance = $this;
    }

    public static function getInstance(){
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

    /**
     * @param string $path 
     * @return this
     */
    public function enableCompilation(string $path)
    {
        if (!file_exists($path)) {
            throw new \RuntimeException("Invalid cache folder {$path}");
        }

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

    public function build()
    {
        if (!$this->isDevMode && !file_exists($this->filename)) {
            $data = parent::getData();
            file_put_contents($this->filename, "<?php return " . var_export($data, true) . ';', LOCK_EX);
        }
    }
}
