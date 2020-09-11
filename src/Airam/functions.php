<?php

namespace Airam;

use Airam\Application;
use DI\ContainerBuilder;
use Dotenv\Dotenv;
use Dotenv\Repository\RepositoryBuilder;
use Dotenv\Repository\Adapter\{EnvConstAdapter, PutenvAdapter};

function applicationFactory($root_dir): Application
{

    $repository = RepositoryBuilder::createWithNoAdapters()
        ->addAdapter(EnvConstAdapter::class)
        ->addWriter(PutenvAdapter::class)
        ->immutable()
        ->make();

    $dir = realpath($root_dir);

    $repository->set('ROOT_DIR', $dir);
    $dotenv = Dotenv::create($repository, $dir);

    $dotenv->load();
    $dotenv->required('ENVIROMENT')->allowedValues(['development', 'production']);
    $dotenv->required('ROOT_DIR');
    $dotenv->required('PAGE_TITLE');

    /** @var Application $app */
    $app = new Application(new ContainerBuilder(),  $dotenv);

    $app->addDefinitions(__DIR__ . '/config/application.php');
    $app->addDefinitions(__DIR__ . '/config/cache.php');
    $app->addDefinitions(__DIR__ . '/config/compiler.php');

    return $app;
};
