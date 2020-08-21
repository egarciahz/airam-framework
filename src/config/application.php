<?php

namespace Core;

//Container
use function DI\create;
use function DI\autowire;
use function DI\factory;
use function DI\get;

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
use Core\Http\Service\RouterProvider;
use Core\Template\Middleware\TemplateHandler;
use Core\Template\Render\Engine as TemplateEngine;
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
use Psr\Container\ContainerInterface;
use Throwable;

/**
 * @return array container application config
 */
return [
    Application::class => autowire(),
    // --
    Route::class => create(),
    Router::class => autowire(),
    RouterProvider::class => autowire(),
    StreamHandler::class => autowire(),
    RouterHandler::class => create()->constructor(get(Router::class)),
    RouteParser::class => create(RouteStdParser::class),
    DataGenerator::class => create(RouterDataGenerator::class),
    // --
    TemplateHandler::class => factory(function (ContainerInterface $c) {
        $instance = new TemplateHandler($c->get(Application::class), function () {
            return new Response();
        });

        return $instance;
    }),
    TemplateEngine::class => create(),
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
    'ServerErrorGenerator' => factory(function () {
        return function (Throwable $error) {
            $isDevMode = Application::isDevMode();
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
