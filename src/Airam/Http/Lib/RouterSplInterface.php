<?php

namespace Airam\Http\Lib;

use Airam\Service\ApplicationService;

interface RouterSplInterface
{
    public function __construct(ApplicationService $proider);
    public function register(): void;
}
