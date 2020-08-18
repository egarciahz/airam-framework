<?php

namespace Core\Http;

use Core\Application;

class Route
{
    static public function get($route, $handler)
    {
        $app = Application::getInstance();
        /** @var Router $router*/
        $router = $app->get(Router::class);
        $router->get($route, $handler);
    }

    static public function post($route, $handler)
    {
        $app = Application::getInstance();
        /** @var Router $router*/
        $router = $app->get(Router::class);
        $router->post($route, $handler);
    }

    static public function put($route, $handler)
    {
        $app = Application::getInstance();
        /** @var Router $router*/
        $router = $app->get(Router::class);
        $router->put($route, $handler);
    }

    static public function delete($route, $handler)
    {
        $app = Application::getInstance();
        /** @var Router $router*/
        $router = $app->get(Router::class);
        $router->delete($route, $handler);
    }

    static public function head($route, $handler)
    {
        $app = Application::getInstance();
        /** @var Router $router*/
        $router = $app->get(Router::class);
        $router->head($route, $handler);
    }

    static public function patch($route, $handler)
    {
        $app = Application::getInstance();
        /** @var Router $router*/
        $router = $app->get(Router::class);
        $router->patch($route, $handler);
    }

    static public function addRoute($httpMethod, $route, $handler)
    {

        $app = Application::getInstance();
        /** @var Router $router*/
        $router = $app->get(Router::class);
        $router->addRoute($httpMethod, $route, $handler);
    }

    static public function addGroup($route, callable $group)
    {
        $app = Application::getInstance();
        /** @var Router $router*/
        $router = $app->get(Router::class);
        $router->addGroup($route, $group);
    }
}
