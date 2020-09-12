<?php

namespace Airam;

//Container
use function DI\create;
use function DI\autowire;
use function DI\factory;
use function DI\get;
use function Airam\Template\Lib\is_renderable;

// Middlewares
use Middlewares\Whoops as WhoopsHandler;

// Whoops
use Whoops\Run as Whoops;
use Whoops\Handler\PrettyPageHandler as WhoopsPrettyPageHandler;

// Application
use Airam\Http\Route;
use Airam\Http\Router;
use Airam\Http\Middleware\RouterHandler;
use Airam\Http\Middleware\ErrorHandler as HttpErrorHandler;
use Airam\Http\Middleware\StatusHandler;
use Airam\Http\Middleware\StreamHandler;
use Airam\Http\Service\RouteService;

use Airam\Service\ApplicationService;
use Airam\Template\Middleware\TemplateHandler;
use Airam\Template\Render\Engine as TemplateEngine;
use Airam\Commons\ApplicationService as ApplicationServiceInterface;
// FastRoute
use FastRoute\DataGenerator\GroupCountBased as RouterDataGenerator;
use FastRoute\RouteParser\Std as RouteStdParser;
use FastRoute\{RouteParser, DataGenerator};
// laminas
use Laminas\Diactoros\Response;
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
    ApplicationService::class => create()->constructor(get(ContainerInterface::class)),
    ApplicationServiceInterface::class => get(ApplicationService::class),
    ApplicationHandler::class => autowire(),
    // --
    Route::class => create(),
    Router::class => autowire(),
    RouteService::class => autowire(),
    StreamHandler::class => autowire(),
    StatusHandler::class => autowire(),
    RouterHandler::class => autowire(),
    // fast-route
    RouteParser::class => create(RouteStdParser::class),
    DataGenerator::class => create(RouterDataGenerator::class),
    // --
    TemplateHandler::class => create()->constructor(get(ContainerInterface::class), function () {
        return new Response;
    }),
    TemplateEngine::class => create()->constructor(get("compiler"), get(ContainerInterface::class)),
    // --
    EmitterStack::class => factory(function (ContainerInterface $c) {
        $stack = new EmitterStack();
        $stack->push(new SapiEmitter());

        return $stack;
    }),
    WhoopsHandler::class => factory(function (ContainerInterface $c) {
        $whoops = new Whoops();
        $responseFactory = new ResponseFactory();

        $page = new WhoopsPrettyPageHandler();
        $page->setPageTitle(getenv("PAGE_TITLE"));
        $page->setEditor("vscode");

        $whoops->pushHandler($page);
        $handler = new WhoopsHandler($whoops, $responseFactory);

        return $handler;
    }),
    MiddlewarePipe::class => factory(function (ContainerInterface $c) {
        $app = new MiddlewarePipe();

        // error handler
        $app->pipe($c->get(WhoopsHandler::class));


        $app->pipe($c->get(ApplicationHandler::class));

        // router handler
        $app->pipe($c->get(RouterHandler::class));

        // middleware stream handler
        $app->pipe($c->get(StreamHandler::class));

        // middleware observer for router data
        $app->pipe($c->get(StatusHandler::class));

        // is module has been added
        if ($module = $c->get(Router::HANDLE_MODULE_CODE)) {
            // if renderable then handler add to stream 
            !is_renderable($module) ?: $app->pipe($c->get(TemplateHandler::class));
        }

        // http-error handler
        $app->pipe(new HttpErrorHandler(function () {
            return Application::isDevMode();
        }));

        return $app;
    }),
    'ServerRequestGenerator' => factory(function () {
        return [ServerRequestFactory::class, 'fromGlobals'];
    }),
    'ServerErrorGenerator' => factory(function (ContainerInterface $c) {
        return function (Throwable $error) {
            $isDevMode = Application::isDevMode();
            $generator = new ErrorResponseGenerator($isDevMode);
            return $generator($error, ServerRequestFactory::fromGlobals(), new Response());
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
