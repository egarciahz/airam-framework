<?php

namespace Airam\Http;

use FastRoute\{RouteCollector, Dispatcher};
use FastRoute\Dispatcher\GroupCountBased;

use Airam\Application;
use Airam\Template\Render\Rendereable;

class Router extends RouteCollector implements Dispatcher
{
    use Rendereable;

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
