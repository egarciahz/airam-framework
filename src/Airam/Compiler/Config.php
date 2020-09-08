<?php

namespace Airam\Commons\Compiler;

class Config
{
    public $dirname;
    public $watch;
    public $subdirs;

    /**
     * @var string $dirname
     * @var string $watch
     * @var array<string> $subdirs
     */
    public function __construct(string $dirname, string $watch, array $subdirs = [])
    {
        $this->dirname = $dirname;
        $this->subdirs = $subdirs;
        $this->watch = $watch;
    }
}
