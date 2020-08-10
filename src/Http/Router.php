<?php

namespace Core\Http;

use Core\Utils\GenericCollection;
use InvalidArgumentException;
use Closure;

class Router extends GenericCollection implements RouterInterface
{
    public function __construct(Route ...$routes)
    {
        parent::__construct($routes);
    }

    public function childrens(): RouterInterface
    {
        $current = $this->current();
        if (!$this->isGroup()) {
            return null;
        }

        return new Router($current->handler);
    }

    public function isGroup(): bool
    {
        $current = $this->current();
        return $current->isNested();
    }

    public function current(): Route
    {
        return parent::current();
    }

    public function offsetGet($offset): ?Route
    {
        return parent::offsetGet($offset);
    }

    public function offsetSet($offset, $value)
    {
        if (!$value instanceof Route) {
            throw new InvalidArgumentException("");
        }

        parent::offsetSet($offset, $value);
    }

    static public function parse(?RouterInterface $router): ?Closure
    {
        if (!$router) {
            return null;
        }

        $collector = function (\FastRoute\RouteCollector $c) use ($router) {
            foreach ($router as $cursor => $route) {
                $c->addRoute($route->method, $route->path, $route->handler);
                //throw new \Error("[$route->path]");
                continue;
                if ($route->isNested()) {
                    $closure = Router::parse(
                        /** handler as childrens */
                        $route->handler
                    );
                    $c->addGroup($route->path, $closure);
                } else {
                    $c->addRoute($route->method, $route->path, $route->handler);
                }
            }
        };

        return $collector;
    }
}
