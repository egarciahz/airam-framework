<?php

namespace Core\Http;

use function DI\get;

class Route
{
    static public function get($route, $handler)
    {
    }

    static public function post($route, $handler)
    {
    }

    static public function put($route, $handler)
    {
    }

    static public function delete($route, $handler)
    {
    }

    static public function head($route, $handler)
    {
    }

    static public function patch($route, $handler)
    {
    }

    static public function addRoute($httpMethod, $route, $handler)
    {
        /** @var Router $router*/
        $router = get(Router::class);
        $router->addRoute($httpMethod, $route, $handler);
    }

    static public function addGroup($route, callable $group)
    {
        /** @var Router $router*/
        $router = get(Router::class);
        $router->addGroup($route, $group);
    }
}
