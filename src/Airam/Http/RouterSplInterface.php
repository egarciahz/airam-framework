<?php

namespace Airam\Http;

use Airam\Http\Service\RouterProvider;

interface RouterSplInterface
{
    public function __construct(RouterProvider $proider);
    public function register(): void;
}
