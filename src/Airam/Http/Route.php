<?php

namespace Airam\Http;

use InvalidArgumentException;
use Laminas\Diactoros\Response;

class Route
{
    static public function get($route, $handler)
    {
        static::addRoute("GET", $route, $handler);
    }

    static public function post($route, $handler)
    {
        static::addRoute("POST", $route, $handler);
    }

    static public function put($route, $handler)
    {
        static::addRoute("PUT", $route, $handler);
    }

    static public function delete($route, $handler)
    {
        static::addRoute("DELETE", $route, $handler);
    }

    static public function head($route, $handler)
    {
        static::addRoute("HEAD", $route, $handler);
    }

    static public function patch($route, $handler)
    {
        static::addRoute("PATH", $route, $handler);
    }

    static public function addRoute($httpMethod, $route, $handler)
    {
        switch (gettype($handler)) {
            case "NULL":
                throw new InvalidArgumentException("Route handler for path {$route} is not defined or NULL.");
                break;
            case "array":
                if (count($handler) === 0) throw new InvalidArgumentException("Route handler array for path {$route} is empty.");
                break;
            case "string":
                if (strlen(trim($handler)) === 0) {
                    throw new InvalidArgumentException("Route handler for path {$route} is an empty string.");
                }
                break;
        }

        $router = Router::getInstance();
        $router->addRoute($httpMethod, $route, $handler);
    }
    
    /**
     * @todo check this functionality
     */
    static public function redirect(string $path): Response
    {
        $response = new Response;
        $response = $response->withHeader("Location", $path)->withStatus(302);
        return $response;
    }

    static public function addGroup($route, callable $group)
    {
        $router = Router::getInstance();
        $router->addGroup($route, $group);
    }
}
