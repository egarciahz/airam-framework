<?php

namespace Core;

//Container
use function DI\create;
use function DI\autowire;
use function DI\factory;

// Middlewares
use Middlewares\Whoops as WhoopsHandler;
use Whoops\Run as Whoops;
use Whoops\Handler\PrettyPageHandler as WhoopsPrettyPageHandler;


// Application
use Core\Http\Route;
use Core\Http\Router;
use Core\Http\Middleware\RouterHandler;
use Core\Http\Middleware\ErrorHandler as HttpErrorHandler;
use Core\Http\Middleware\StreamHandler;
use Core\Service\RouterServiceProvider;
// FastRoute
use FastRoute\RouteParser\Std as RouteStdParser;
use FastRoute\DataGenerator\GroupCountBased as RouterDataGenerator;
use FastRoute\{RouteParser, DataGenerator};
// laminas
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\Diactoros\ResponseFactory;
use Laminas\HttpHandlerRunner\Emitter\EmitterStack;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Laminas\HttpHandlerRunner\RequestHandlerRunner;
use Laminas\Stratigility\Middleware\ErrorResponseGenerator;
use Laminas\Stratigility\MiddlewarePipe;
use Middlewares\Utils\CallableHandler;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

/**
 * @return array container application config
 */
return [
    Route::class => create(),
    Router::class => autowire(),
    RouterServiceProvider::class => autowire(),
    StreamHandler::class => autowire(),
    RouterHandler::class => factory(function (ContainerInterface $c) {
        $router = $c->get(Router::class);
        return new RouterHandler(function () {
            return new Response();
        }, $router);
    }),

    RouteParser::class => create(RouteStdParser::class),
    DataGenerator::class => create(RouterDataGenerator::class),
    // --
    Application::class => autowire(),
    ApplicationInterface::class => autowire(Application::class),
    // --
    EmitterStack::class => factory(function (ContainerInterface $c) {
        $stack = new EmitterStack();
        $stack->push(new SapiEmitter());
        // emitters 
        // ...
        return $stack;
    }),
    WhoopsHandler::class => factory(function (ContainerInterface $c) {
        $whoops = new Whoops();
        $conf = $c->get("app.config");

        $responseFactory = new ResponseFactory();

        $page = new WhoopsPrettyPageHandler();
        $page->setPageTitle($conf["name"]);
        $page->setEditor("vscode");

        $whoops->pushHandler($page);
        $handler = new WhoopsHandler($whoops, $responseFactory);

        return $handler;
    }),
    //
    MiddlewarePipe::class => factory(function (ContainerInterface $c) {
        $app = new MiddlewarePipe();

        // error handler
        $app->pipe($c->get(WhoopsHandler::class));

        // router handler
        $app->pipe($c->get(RouterHandler::class));

        // middleware stream handler
        $app->pipe($c->get(StreamHandler::class));

        // http-error handler
        $app->pipe(new HttpErrorHandler(function () {
            return new Response();
        }));

        return $app;
    }),
    'ServerRequestGenerator' => factory(function () {
        return [ServerRequestFactory::class, 'fromGlobals'];
    }),
    'ServerErrorGenerator' => factory(function (ContainerInterface $container) {
        $isDevMode = Application::isDevMode();
        return function (Throwable $error) use ($isDevMode) {
            $generator = new ErrorResponseGenerator($isDevMode);
            return $generator($error, new ServerRequest(), new Response());
        };
    }),
    RequestHandlerRunner::class => factory(function (ContainerInterface $container) {
        $handler = new RequestHandlerRunner(
            $container->get(MiddlewarePipe::class),
            $container->get(EmitterStack::class),
            $container->get('ServerRequestGenerator'),
            $container->get('ServerErrorGenerator')
        );

        return $handler;
    })
];
