<?php

use Core\Application;
use App\Http\RouterModule;

include '../vendor/autoload.php';

/** @var Application $app */
$appFactory = require '../src/bootstrap.php';
$app = $appFactory(__DIR__ . "/../");

$app->addDefinitions(getenv('ROOT_DIR') . "/app/config/app.php");

if (getenv("ENVIROMENT") === 'production') {
    $app->enableProdMode();
}

$app->addRouterModule(RouterModule::class);

$app();
