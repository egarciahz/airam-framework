<?php

namespace Airam;

use Airam\Http\RouterSplInterface;
use Airam\Template\Render\Engine as TemplateEngine;
use DI\{Container, ContainerBuilder};
use Dotenv\Dotenv;

use Laminas\HttpHandlerRunner\RequestHandlerRunner;
use InvalidArgumentException;
use function DI\autowire;


class Application implements ApplicationInterface
{

    /** @var string $router_module_class RouterModule classpath */
    private $router_module_class = null;

    /** @var bool $production */
    private static $production = false;

    /** @var Container|ContainerBuilder $builder */
    private $builder;

    /** @var Container $container */
    public $container;

    /** @var Application $me*/
    private static $me;

    /** @var Dotenv $env */
    public $env;

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

            $this->builder->enableCompilation($root . '/.cache/build');
            $this->builder->enableDefinitionCache("App\Cache");
            $this->builder->writeProxiesToFile(true, $root . '/.cache/tmp');
            $this->builder->ignorePhpDocErrors(true);
        }
    }

    public function addDefinitions(...$definitions): void
    {
        if (!$this->container) {
            $this->builder->addDefinitions(...$definitions);
        }
    }

    public function addRouterModule($module_class): void
    {
        if (false == array_search(RouterSplInterface::class, class_implements($module_class))) {
            throw new InvalidArgumentException("RouterModule [$module_class] is not an implementation of RouterSplInterface");
        }

        $this->router_module_class = $module_class;
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

    public function run()
    {

        if (!($this->container instanceof Container)) {
            $this->container = $this->builder->build();
        }

        $this->container->set(self::class, $this);
        $this->container->set("AppMainRouterModule", autowire($this->router_module_class));
        
        $engine = $this->container->get(TemplateEngine::class);
        $engine->build(self::$production);

        /** @var RequestHandlerRunner $runner */
        $runner = $this->container->get(RequestHandlerRunner::class);
        $runner->run();

        return $this->container;
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
