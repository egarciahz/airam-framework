<?php

namespace Airam\Http;

use Airam\Application;
use InvalidArgumentException;

class Route
{
    static public function get($route, $handler)
    {
        self::addRoute("GET", $route, $handler);
    }

    static public function post($route, $handler)
    {
        self::addRoute("POST", $route, $handler);
    }

    static public function put($route, $handler)
    {
        self::addRoute("PUT", $route, $handler);
    }

    static public function delete($route, $handler)
    {
        self::addRoute("DELETE", $route, $handler);
    }

    static public function head($route, $handler)
    {
        self::addRoute("HEAD", $route, $handler);
    }

    static public function patch($route, $handler)
    {
        self::addRoute("PATH", $route, $handler);
    }

    static public function addRoute($httpMethod, $route, $handler)
    {
        switch (gettype($handler)) {
            case "NULL":
                throw new InvalidArgumentException("Route handler for path {$route} is not defined or NULL.");
            case "array":
                if (count($handler) === 0) throw new InvalidArgumentException("Route handler array for path {$route} is empty.");
            case "string":
                if (strlen(trim($handler)) === 0) {
                    throw new InvalidArgumentException("Route handler for path {$route} is an empty string.");
                }
                if (!class_exists($handler) && !is_callable($handler)) {
                    throw new InvalidArgumentException("Route handler from path {$route} we don't use an existing class or a callable.");
                }
        }

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
