<?php

namespace Core;

use function DI\create;
use function DI\autowire;
use function DI\factory;

use Core\Http\Route;
use Core\Http\Router;
use Core\Http\Middleware\RouterHandler;
use Core\Http\Middleware\ErrorHandler as HttpErrorHandler;
use Core\Service\RouterServiceProvider;
use Core\Template\TemplateProcessorInterface;
use Core\Template\TemplateProcessorRuntime;
use Core\Template\TemplateProcessor;
//
use FastRoute\RouteParser\Std as RouteStdParser;
use FastRoute\DataGenerator\GroupCountBased as RouterDataGenerator;
use FastRoute\{RouteParser, DataGenerator};
// laminas
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\EmitterStack;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Laminas\HttpHandlerRunner\RequestHandlerRunner;
use Laminas\Stratigility\Middleware\ErrorHandler;
use Laminas\Stratigility\Middleware\ErrorResponseGenerator;
use Laminas\Stratigility\MiddlewarePipe;
use Psr\Container\ContainerInterface;
use Throwable;

/**
 * @return array container application config
 */
return [
    Route::class => create(),
    Router::class => autowire(),
    RouterServiceProvider::class => autowire(),

    RouteParser::class => create(RouteStdParser::class),
    DataGenerator::class => create(RouterDataGenerator::class),

    FilesystemCache::class => create(),
    Application::class => autowire(),
    ApplicationInterface::class => autowire(Application::class),
    //
    TemplateProcessorInterface::class => create(TemplateProcessor::class),
    TemplateProcessorRuntime::class => create(),
    TemplateProcessor::class => autowire(),
    EmitterStack::class => factory(function (ContainerInterface $c) {
        $stack = new EmitterStack();
        $stack->push(new SapiEmitter());
        // emitters 
        // ...
        return $stack;
    }),
    //
    MiddlewarePipe::class => factory(function (ContainerInterface $c) {
        $isDevMode = Application::isDevMode();
        $app = new MiddlewarePipe();

        // middlewares
        $app->pipe(new ErrorHandler(function () {
            return new Response();
        }, new ErrorResponseGenerator($isDevMode)));

        // route handler
        $app->pipe(new RouterHandler(
            function () {
                return new Response();
            },
            $c->get(Router::class)
        ));

        // not found router
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
