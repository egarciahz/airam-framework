<?php

namespace Airam;

use Airam\Http\Router;
use Airam\Http\Lib\RouterSplInterface;
use Airam\Template\Render\Engine as TemplateEngine;
use DI\{Container, ContainerBuilder};
use Dotenv\Dotenv;

use Laminas\HttpHandlerRunner\RequestHandlerRunner;
use InvalidArgumentException;
use RuntimeException;

use function DI\autowire;


class Application implements ApplicationInterface
{

    /** @var bool $production */
    private static $production = false;

    /** @var ContainerBuilder $builder */
    private $builder;

    /** @var Container $container */
    public $container;

    /** @var Application $me*/
    private static $me;

    /** @var Dotenv $env */
    public $env;

    /** @var Router $router */
    private $router;

    /** @var TemplateEngine $engine */
    private $engine;

    /**
     * @param ContainerBuilder $builder
     * 
     * @return void
     */
    public function __construct(ContainerBuilder $builder)
    {
        self::$me = $this;

        $this->builder = $builder;
        $this->builder->useAnnotations(true);
        $this->builder->useAutowiring(true);
    }

    public function enableProdMode(): void
    {
        self::$production = true;
        /** @var string $root */
        $root = getenv('ROOT_DIR');
        if ($this->builder instanceof ContainerBuilder) {

            $this->builder->enableCompilation("{$root}/.cache/build");
            $this->builder->enableDefinitionCache("Airam\Cache");
            $this->builder->writeProxiesToFile(true, "tmp/proxies");
            $this->builder->ignorePhpDocErrors(true);
            $this->container = $this->builder->build();

            $this->engine = $this->container->get(TemplateEngine::class);
            $this->engine->enableCompilation("{$root}/.cache/render");

            $this->router = $this->container->get(Router::class);
            $this->router->enableCompilation("{$root}/.cache/");
        }
    }

    public function addDefinitions(...$definitions): void
    {
        if (!$this->container) {
            $this->builder->addDefinitions(...$definitions);
        }
    }

    public function addRouterModule($class_name): void
    {
        if (!class_exists($class_name)) {
            throw new RuntimeException("Don't exist class name {$class_name}.");
        }

        if (false == array_search(RouterSplInterface::class, class_implements($class_name))) {
            throw new RuntimeException("RouterModule [$class_name] is not an implementation of RouterSplInterface");
        }

        if (!$this->container) {
            $this->builder->addDefinitions([Router::HANDLE_MODULE_CODE => autowire($class_name)]);
        }
    }

    public function isProdMode(): bool
    {
        return self::$production;
    }

    public function get($id)
    {
        return $this->container->get($id);
    }

    public function has($id)
    {
        return $this->container->has($id);
    }

    public function run(): void
    {

        if (!($this->container instanceof Container)) {
            $this->container = $this->builder->build();
        }

        $this->container->set(self::class, $this);

        if (!($this->router instanceof Router)) {
            $this->router = $this->container->get(Router::class);
        }

        if (!($this->engine instanceof TemplateEngine)) {
            $this->engine = $this->container->get(TemplateEngine::class);
        }

        /** @var RequestHandlerRunner $runner */
        $runner = $this->container->get(RequestHandlerRunner::class);

        $this->router->build();
        $this->engine->build();
        $runner->run();
    }

    public static function isDevMode(): bool
    {
        return  !self::$production;
    }

    public static function getInstance()
    {
        return self::$me;
    }
}
