<?php

namespace Core;

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Core\Http\RouterSplInterface;

interface ApplicationInterface extends ContainerInterface
{
    /**
     * @param ContainerBuilder $container
     * 
     * @return void
     */
    public function __construct(ContainerBuilder $builder);
    public function addRouterModule(RouterSplInterface $classname): void;
    public function addDefinitions(...$definitions): void;
    public function enableProdMode(): void;
    public function isProdMode(): bool;

    public function __invoke();
}
