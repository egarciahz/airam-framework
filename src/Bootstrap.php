<?php

namespace Core;

use DI\{Container, ContainerBuilder};
use Laminas\HttpHandlerRunner\RequestHandlerRunner;
use Core\BootstrapApplicationInterface;
use InvalidArgumentException;
use RuntimeException;

class Bootstrap implements BootstrapApplicationInterface
{

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
            throw new InvalidArgumentException("\$builder is not a valid param, extpected DI\Container or DI\ContainerBuilder: ");
        }

        $builder->addDefinitions(__DIR__ . '/../config/application.php');
        $this->builder = $builder;
    }

    public function enableProdMode()
    {
        self::$production = true;
        /** @var string $root */
        $root = getenv('ROOT_DIR');

        if ($this->builder instanceof ContainerBuilder) {

            $this->builder->enableCompilation($root . '/.cache');
            $this->builder->writeProxiesToFile(true, $root . '/.cache');
            $this->builder->ignorePhpDocErrors(true);

            $this->container = $this->builder->build();
        }
    }

    public static function isDevMode()
    {
        return  !self::$production;
    }

    public function __invoke()
    {

        if (!$this->container instanceof Container) {
            $this->container = $this->builder->build();
        }

        /** @var RequestHandlerRunner $runner */
        $runner = $this->container->get('RequestHandlerRunner');

        $runner->run();
    }
}
