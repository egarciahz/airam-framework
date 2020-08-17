<?php
date_default_timezone_set('UTC');

use Core\Application;
use Core\ApplicationInterface;
use DI\ContainerBuilder;
use Dotenv\Repository\RepositoryBuilder;
use Dotenv\Repository\Adapter\{EnvConstAdapter, PutenvAdapter};

return function ($dir): ApplicationInterface {

    $repository = RepositoryBuilder::createWithNoAdapters()
        ->addAdapter(EnvConstAdapter::class)
        ->addWriter(PutenvAdapter::class)
        ->immutable()
        ->make();

    $repository->set('ROOT_DIR', $dir);
    $dotenv = Dotenv\Dotenv::create($repository, $dir);

    $dotenv->load();
    $dotenv->required('ENVIROMENT')->allowedValues(['development', 'production']);
    $dotenv->required('ROOT_DIR');

    /** @var Application $app */
    $app = new Application(new ContainerBuilder());

    $app->addDefinitions(__DIR__ . '/config/application.php');
    $app->addDefinitions(__DIR__ . '/config/global.php');

    return $app;
};
