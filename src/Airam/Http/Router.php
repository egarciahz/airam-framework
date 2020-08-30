<?php

namespace Airam\Http;

use FastRoute\{RouteCollector, Dispatcher};
use FastRoute\Dispatcher\GroupCountBased;

use Airam\Template\Render\Rendereable;

class Router extends RouteCollector implements Dispatcher
{
    use Rendereable;

    const HANDLE_STATUS_CODE = "0x_RouterStatusData.Code$";
    const HANDLE_MODULE_CODE = "1x_RouterModuleRef.Code$";
    
    public function dispatch($httpMethod, $uri)
    {
        $base = new GroupCountBased($this->getData());
        return $base->dispatch($httpMethod, $uri);
    }
}
