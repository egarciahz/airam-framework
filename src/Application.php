<?php

namespace Core;

use Core\Http\RouterSplInterface;
use DI\{Container, ContainerBuilder};
use Laminas\HttpHandlerRunner\RequestHandlerRunner;
use InvalidArgumentException;


class Application implements ApplicationInterface
{

    /** @var string $router_module_class RouterModule classpath */
    private $router_module_class;

    /** @var bool $production */
    private static $production = false;

    /** @var Container|ContainerBuilder $builder */
    private $builder;

    /** @var Container $container */
    public $container;

    /**
     * @param ContainerBuilder $builder
     * 
     * @return void
     */
    public function __construct(ContainerBuilder $builder)
    {
        if (!($builder instanceof ContainerBuilder) && !($builder instanceof Container)) {
            throw new InvalidArgumentException("builder is not a valid param, extpected DI\ContainerBuilder");
        }

        $this->builder = $builder;
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
            throw new InvalidArgumentException("addRouterModule module is not RouterSplInterface implementation");
        }

        $this->router_module_class = $module_class;
    }

    public static function isDevMode(): bool
    {
        return  !self::$production;
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

    public function __invoke()
    {

        if (!($this->container instanceof Container)) {
            //$this->builder->useAnnotations(true);
            $this->builder->useAutowiring(true);

            $this->container = $this->builder->build();
        }

        $this->container->set(self::class, $this);

        if (!self::$production && $this->router_module_class) {
            $routerModule = $this->container->get($this->router_module_class);
            $routerModule->register();
        }

        /** @var RequestHandlerRunner $runner */
        $runner = $this->container->get(RequestHandlerRunner::class);
        $runner->run();

        return $this->container;
    }
}
