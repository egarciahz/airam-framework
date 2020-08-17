<?php

namespace Core\Service;

use Core\Application;
use Core\Http\Router;

class RouterServiceProvider
{
    private $app;
    private $router;

    public function __construct(Application $app, Router $router)
    {
        $this->app = $app;
        $this->router = $router;
    }

    public function router(): Router
    {
        return $this->router;
    }

    public function register(string $path)
    {
        if (!$this->app->isProdMode()) {
            require $path;
        }
    }
}
