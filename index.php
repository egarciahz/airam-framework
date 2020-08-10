<?php

include 'vendor/autoload.php';
date_default_timezone_set('UTC');

use Core\Services\Bootstrap;
use DI\ContainerBuilder;
use Dotenv\Repository\RepositoryBuilder;
use Dotenv\Repository\Adapter\{EnvConstAdapter, PutenvAdapter};

$repository = RepositoryBuilder::createWithNoAdapters()
    ->addAdapter(EnvConstAdapter::class)
    ->addWriter(PutenvAdapter::class)
    ->immutable()
    ->make();

$repository->set('ROOT_DIR', __DIR__);
$dotenv = Dotenv\Dotenv::create($repository, __DIR__);

$dotenv->load();
$dotenv->required('ENVIROMENT')->allowedValues(['development', 'production']);
$dotenv->required('ROOT_DIR');

/** @var Bootstrap $app */
$app = new Bootstrap(new ContainerBuilder());

if (getenv("ENVIROMENT") === 'production') {
    $app->enableProdMode();
}

$app();