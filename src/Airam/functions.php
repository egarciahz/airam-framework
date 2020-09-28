<?php

namespace Airam;

/** 
 * default namespace name for Airam proxy files
 */
define("AIRAM_PROXY_NAMESPACE", "Airam\\Proxy");

use Airam\Application;

use DI\ContainerBuilder;
use Dotenv\Dotenv;
use Dotenv\Repository\RepositoryBuilder;
use Dotenv\Repository\Adapter\{
    EnvConstAdapter,
    PutenvAdapter
};

use function Airam\Commons\path_join;
use Exception;


function applicationFactory($root_dir): Application
{

    $dir = realpath($root_dir);
    /**
     * application root directory
     */
    define("ROOT_DIR", $dir);

    try {
        $repository = RepositoryBuilder::createWithNoAdapters()
            ->addAdapter(EnvConstAdapter::class)
            ->addWriter(PutenvAdapter::class)
            ->immutable()
            ->make();

        $repository->set('ROOT_DIR', $dir);
        $dotenv = Dotenv::create($repository, $dir);

        $dotenv->load();
        $dotenv->required('ENVIRONMENT')->allowedValues(['development', 'production']);
        $dotenv->required('ROOT_DIR');
        $dotenv->required('APP_NAME');

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
        header("Content-Type: text/html");
        header("HTTP/1.1 500 Server Error");
        echo $response;
        exit;
    }

    /** @var Application $app */
    $app = new Application(new ContainerBuilder(),  $dotenv);

    $app->addDefinitions(__DIR__ . '/config/application.php');
    $app->addDefinitions(__DIR__ . '/config/compiler.php');
    $app->addDefinitions(__DIR__ . '/ORM/config.php');

    return $app;
};
