<?php

namespace Core\Http;

use FastRoute\{RouteCollector, Dispatcher};
use FastRoute\Dispatcher\GroupCountBased;
use Core\Application;

class Router extends RouteCollector implements Dispatcher
{
    /**
     * @Inject
     * @var Application $app
     */
    private $app;

    public function dispatch($httpMethod, $uri)
    {
       
        $base = new GroupCountBased($this->getData());

        return $base->dispatch($httpMethod, $uri);
    }
}
