<?php

namespace Core;

use Core\Application;
use DI\ContainerBuilder;
use Dotenv\Dotenv;
use Dotenv\Repository\RepositoryBuilder;
use Dotenv\Repository\Adapter\{EnvConstAdapter, PutenvAdapter};

function applicationFactory($dir): Application
{

    $repository = RepositoryBuilder::createWithNoAdapters()
        ->addAdapter(EnvConstAdapter::class)
        ->addWriter(PutenvAdapter::class)
        ->immutable()
        ->make();

    $repository->set('ROOT_DIR', $dir);
    $dotenv = Dotenv::create($repository, $dir);

    $dotenv->load();
    $dotenv->required('ENVIROMENT')->allowedValues(['development', 'production']);
    $dotenv->required('ROOT_DIR');

    /** @var Application $app */
    $app = new Application(new ContainerBuilder());

    $app->addDefinitions(__DIR__ . '/config/application.php');
    $app->addDefinitions(__DIR__ . '/config/global.php');

    return $app;
};
