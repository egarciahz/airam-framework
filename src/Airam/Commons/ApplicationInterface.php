<?php

namespace Airam\Commons;

use DI\ContainerBuilder;
use Dotenv\Dotenv;
use Psr\Container\ContainerInterface;

interface ApplicationInterface
{
    /**
     * @param ContainerBuilder $container
     * @param Dotenv $env
     * 
     * @return void
     */
    public function __construct(ContainerBuilder $builder, Dotenv $env);
    public function addRouterModule($classname): void;
    public function addDefinitions(...$definitions): void;

    public function enableProdMode(): void;

    public function isProdMode(): bool;
    public static function isDevMode(): bool;

    public function getDotenv(): Dotenv;
    public function build(): ContainerInterface;
    public function run();
}
