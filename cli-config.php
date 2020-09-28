<?php
include 'vendor/autoload.php';

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Console\ConsoleRunner;

use function Airam\applicationFactory;

date_default_timezone_set('UTC');
$root = __DIR__;

$app = applicationFactory($root);

$app->addDefinitions($root . "/app/config/app.php");

$container = $app->build();

$Manager = $container->get(EntityManager::class);

return ConsoleRunner::createHelperSet($Manager);