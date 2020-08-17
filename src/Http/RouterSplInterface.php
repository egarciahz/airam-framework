<?php

namespace Core\Http;

use Core\Service\RouterServiceProvider;

interface RouterSplInterface
{
    public function __construct(RouterServiceProvider $proider);
    public function register(): void;
}
