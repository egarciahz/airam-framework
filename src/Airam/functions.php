<?php

namespace Airam;

use Airam\Application;
use DI\ContainerBuilder;
use Dotenv\Dotenv;
use Dotenv\Repository\RepositoryBuilder;
use Dotenv\Repository\Adapter\{EnvConstAdapter, PutenvAdapter};
use Exception;

use function Airam\Commons\path_join;

function applicationFactory($root_dir): Application
{

    $repository = RepositoryBuilder::createWithNoAdapters()
        ->addAdapter(EnvConstAdapter::class)
        ->addWriter(PutenvAdapter::class)
        ->immutable()
        ->make();

    try {
        $dir = realpath($root_dir);
        define("ROOT_DIR", $dir);
        
        $repository->set('ROOT_DIR', $dir);
        $dotenv = Dotenv::create($repository, $dir);

        $dotenv->load();
        $dotenv->required('ENVIRONMENT')->allowedValues(['development', 'production']);
        $dotenv->required('ROOT_DIR');
        
    } catch (Exception $error) {
     
        /**
         * variable definitions for errorTemplate
         */
        $code = 500;
        $note = path_join(DIRECTORY_SEPARATOR, $dir, ".env");
        $title = (!file_exists($note) ? "File <small>.env</small> don't exist." : (!is_readable($note) ? "Configuration file <small>.env</small> is not readable." : "Server Error."));
        $message = "Bad Environment";
        $description = $error->getMessage();
        // generate view
        ob_start();
        require __DIR__ . '/Http/Resources/errorTemplate.php';
        $response = ob_get_clean();
        // response to the client
        header("HTTP/1.1 500 Server Error");
        echo var_export($_ENV, true); //$response;
        exit;
    }
    /** @var Application $app */
    $app = new Application(new ContainerBuilder(),  $dotenv);

    $app->addDefinitions(__DIR__ . '/config/application.php');
    $app->addDefinitions(__DIR__ . '/config/compiler.php');
    //$app->addDefinitions(__DIR__ . '/config/orm.php');

    return $app;
};
