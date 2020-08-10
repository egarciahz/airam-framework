<?php

namespace App\Http;

use Core\Http\Route;
use Core\Http\Router;

return new Router(
    new Route('POST', '/', 'App\Client\Test')
);
