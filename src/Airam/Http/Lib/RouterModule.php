<?php

namespace Airam\Http\Lib;

use Airam\Commons\ApplicationService;

interface RouterModule
{
    public function __construct(ApplicationService $proider);
    /**
     * @return array<string>
     */
    public function register(): array;
}
