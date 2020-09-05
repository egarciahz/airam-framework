<?php

namespace Airam\Commons\Compiler;

class DataTokens
{
    /** @var string $code */
    public $code;

    /** @var array<string> $usages */
    public $usages = [];

    /** @var string|null $namespaceName */
    public $namespaceName = null;

    /** @var string $filename */
    public $filename;

    /** @var DirMap $dirMap */
    public $dirMap;
}
