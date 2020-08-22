<?php

namespace Core\Http;

use Core\Http\Service\RouterProvider;

interface RouterSplInterface
{
    public function __construct(RouterProvider $proider);
    public function register(): void;
}
