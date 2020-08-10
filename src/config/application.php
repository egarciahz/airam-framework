<?php

namespace Core;

use function DI\create;
use function DI\autowire;
use function DI\factory;
use function FastRoute\simpleDispatcher;

use Core\Bootstrap;
use Core\FilesystemCache;
use Core\Http\ErrorHandler as HttpErrorHandler;
use Core\Http\Route;
use Core\Http\Router;
use Core\Http\RouterHandler;
use Core\Http\RouterInterface;
use Core\Template\TemplateProcessorInterface;
use Core\Template\TemplateProcessorRuntime;
use Core\Template\TemplateProcessor;
use FastRoute\RouteCollector;
// laminas
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\EmitterStack;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Laminas\HttpHandlerRunner\RequestHandlerRunner;
use Laminas\Stratigility\Middleware\ErrorHandler;
use Laminas\Stratigility\Middleware\ErrorResponseGenerator;
use Laminas\Stratigility\Middleware\NotFoundHandler;
use Laminas\Stratigility\MiddlewarePipe;
use Psr\Container\ContainerInterface;
use Throwable;

/**
 * @return array container application config
 */
return [
    FilesystemCache::class => create(),
    Bootstrap::class => autowire(),
    //
    TemplateProcessorInterface::class => create(TemplateProcessor::class),
    TemplateProcessorRuntime::class => create(),
    TemplateProcessor::class => autowire(),
    //
    RouterInterface::class => create(Router::class),
    Router::class  => create(Router::class),
    //
    EmitterStack::class => factory(function (ContainerInterface $c) {
        $stack = new EmitterStack();
        $stack->push(new SapiEmitter());
        // emitters 
        return $stack;
    }),
    //
    MiddlewarePipe::class => factory(function (ContainerInterface $c) {
        $isDevMode = Bootstrap::isDevMode();
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
            function () {
                return simpleDispatcher(function (RouteCollector $c) {
                    $c->addRoute("GET", '/', App\Client\Test::class);
                });
            }
        ));
        // not found router
        $app->pipe(new NotFoundHandler(function () {
            return new Response();
        }));

        return $app;
    }),
    'ServerRequestGenerator' => factory(function () {
        return [ServerRequestFactory::class, 'fromGlobals'];
    }),
    'ServerErrorGenerator' => factory(function (ContainerInterface $container) {
        return function (Throwable $error) {
            $isDevMode = Bootstrap::isDevMode();
            $generator = new ErrorResponseGenerator($isDevMode);
            return $generator($error, new ServerRequest(), new Response());
        };
    }),
    'RequestHandlerRunner' => factory(function (ContainerInterface $container) {
        $handler = new RequestHandlerRunner(
            $container->get(MiddlewarePipe::class),
            $container->get(EmitterStack::class),
            $container->get('ServerRequestGenerator'),
            $container->get('ServerErrorGenerator')
        );

        return $handler;
    }),
    'RouterDispatch' => factory(function (ContainerInterface $container) {
    }),
];
