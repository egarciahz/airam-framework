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

    private $layout = [
        "notFound" => null,
        "layout" => null,
        "error" => null
    ];

    public function configure(array $options)
    {
        $keys = array_keys($this->layout);
        $options = array_filter($options, function ($value) use ($keys) {
            return array_search($value, $keys) !== false;
        });

        $this->layout = array_merge($this->layout, $options);
    }

    public function getLayout()
    {
        return $this->layout['layout'];
    }

    public function getNotFoundView()
    {
        return $this->layout['notFound'] || ["<h3 style='color:gray;'>%d Route Not Found</h3> <p>%s</p>"];
    }

    public function getHttpErrorView()
    {
        return $this->layout['error'] || ["<h3 style='color:orangered;'>Error %d</h3> <span style='color:gray;'><b>%s</b> <i>%s</i></span> <p>%s</p>"];
    }

    public function dispatch($httpMethod, $uri)
    {
        $base = new GroupCountBased($this->getData());
        return $base->dispatch($httpMethod, $uri);
    }
}
