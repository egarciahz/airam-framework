<?php

namespace Airam;

use Airam\Http\Router;
use Airam\Http\Lib\RouterSplInterface;
use Airam\Template\Render\Engine as TemplateEngine;
use Airam\Commons\ApplicationInterface;

use DI\{Container, ContainerBuilder};
use Dotenv\Dotenv;

use Laminas\HttpHandlerRunner\RequestHandlerRunner;
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

    /** @var Dotenv $env */
    private $env;

    /** @var Router $router */
    private $router;

    /** @var TemplateEngine $engine */
    private $engine;

    /**
     * @param ContainerBuilder $builder
     * 
     * @return void
     */
    public function __construct(ContainerBuilder $builder, Dotenv $env)
    {
        $this->env = $env;

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

            $this->container = $this->builder->enableCompilation("{$root}/.cache/build")
                //->enableDefinitionCache("Airam\Cache")
                ->writeProxiesToFile(true, "{$root}/.cache/tmp/proxies")
                ->ignorePhpDocErrors(true)
                ->build();

            $this->router = $this->container->get(Router::class);
            $this->router->enableCompilation("{$root}/.cache/build");

            $this->engine = $this->container->get(TemplateEngine::class);
            $this->engine->enableCompilation("{$root}/.cache/render");
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

    private function logStartUp()
    {
        $log = [
            "*****************************************",
            "*",
            "* Airam its running!!",
            "*",
            "* Running on : " . ($_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"]),
            "* Production : " . (self::$production ? "Enabled" : "Disabled"),
            "* Root Folder: " . $_SERVER["DOCUMENT_ROOT"],
            "*",
            "*****************************************"
        ];

        error_log("\n" . join("\n", $log));
    }

    public function getDotenv(): Dotenv
    {
        return $this->env;
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

        /** @var RouterSplInterface $module */
        $module = $this->container->get(Router::HANDLE_MODULE_CODE);
        $module->register();

        /** @var RequestHandlerRunner $runner */
        $runner = $this->container->get(RequestHandlerRunner::class);
        $this->logStartUp();
        $this->router->build();
        $this->engine->build();
        $runner->run();
    }

    public static function isDevMode(): bool
    {
        return  !self::$production;
    }
}
