<?php

namespace Airam;

use Airam\Http\Router;
use Airam\Http\Lib\RouterSplInterface;
use Airam\Template\Render\Engine as TemplateEngine;
use Airam\Commons\ApplicationInterface;
use Airam\Compiler\{Compilable, Compiler};
use Airam\Compiler\Config;

use DI\{Container, ContainerBuilder};
use Laminas\HttpHandlerRunner\RequestHandlerRunner;
use Psr\Container\ContainerInterface;
use Dotenv\Dotenv;

use function Airam\Commons\loadResource;
use function Airam\Commons\path_join;
use function DI\autowire;

use RuntimeException;

class Application implements ApplicationInterface
{
    /** @var bool $enableCompilationAT */
    private  $enableCompilationAT = false;

    /** @var bool $production */
    private static $production = false;

    /** @var ContainerBuilder $builder */
    private $builder;

    /** @var Container $container */
    public $container;

    /** @var Dotenv $env */
    private $env;

    /**
     * @param ContainerBuilder $builder
     * 
     * @return void
     */
    public function __construct(ContainerBuilder $builder, Dotenv $env)
    {
        $this->env = $env;
        $this->enableCompilationAT = getenv("AT_COMPILATION") === "enabled";

        $this->builder = $builder;
        $this->builder->useAnnotations(true);
        $this->builder->useAutowiring(true);
    }

    public function enableProdMode(): void
    {
        self::$production = true;

        $data = loadResource(path_join(DIRECTORY_SEPARATOR, __DIR__, "config", "compiler.php"));
        $config = Config::fromArray($data['compiler']['config']);
        // check that compilation at time is enabled
        $this->enableCompilationAT ? $config->buildAt() : $config->build();
    }

    public function build(): ContainerInterface
    {
        /** 
         * Is applicatione is production
         */
        define("AIRAM_PRODUCTION_MODE", self::$production);
        // enabble logger for compiler
        Compiler::$enable_log = !self::$production || $this->enableCompilationAT;

        $root = ROOT_DIR;
        if (!($this->container instanceof Container)) {
            if (static::$production) {

                $this->container = $this->builder->enableCompilation("{$root}/.cache/build")
                    ->enableDefinitionCache(AIRAM_PROXY_NAMESPACE)
                    ->writeProxiesToFile(true, "{$root}/.cache/tmp/proxies")
                    ->ignorePhpDocErrors(true)
                    ->build();

                $router = $this->container->get(Router::class);
                $router->enableCompilation("{$root}/.cache/build");

                $engine = $this->container->get(TemplateEngine::class);
                $engine->enableCompilation();
            } else {

                $this->container = $this->builder->build();
            }
        }

        $this->container->set(self::class, $this);
        $this->container->set("ProductionMode", static::$production);

        return $this->container;
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
            "* Airam Framework",
            "*",
            "* Running on : " . ($_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"]),
            "* Production : " . (self::$production ? "Enabled" : "Disabled"),
            "* Root Folder: " . $_SERVER["DOCUMENT_ROOT"],
            "*",
            "*****************************************"
        ];

        $this->isProdMode() ?: error_log("\n" . join("\n", $log));
    }

    public function getDotenv(): Dotenv
    {
        return $this->env;
    }

    public function run(): void
    {
        $this->build();

        /** @var RequestHandlerRunner $runner */
        $runner = $this->container->get(RequestHandlerRunner::class);
        $runner->run();

        $this->logStartUp();
    }

    public static function isDevMode(): bool
    {
        return  !self::$production;
    }
}
