<?php
include 'vendor/autoload.php';

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Console\ConsoleRunner;

use function Airam\applicationFactory;

date_default_timezone_set('UTC');
$root = __DIR__;

$app = applicationFactory($root);
// database environment variables
$dotenv = $app->getDotenv();
$dotenv->required("DB_DRIVER");
$dotenv->required("DB_USER");
$dotenv->required("DB_PASSWORD");
$dotenv->required("DB_NAME");

$app->addDefinitions($root . "/app/config/app.php");

$container = $app->build();

$em = $container->get(EntityManager::class);

return ConsoleRunner::createHelperSet($em);