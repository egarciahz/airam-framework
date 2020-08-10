<?php

namespace Core;

use DI\ContainerBuilder;

interface BootstrapApplicationInterface
{
    /**
     * @param ContainerBuilder $container
     * 
     * @return void
     */
    public function __construct(ContainerBuilder $builder);
    public function enableProdMode();

    public function __invoke();
}
