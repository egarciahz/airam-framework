<?php

namespace Airam\Commons\Compiler;

class DirMap
{
    /** @var Config $config*/
    private $config;

    /** @var string $target*/
    private $target;

    /** @var string $watch*/
    private $watch;

    /** @var array<string> $files list of available file extensions*/
    public $files = [];

    /** @var array<string> $exclude exclude directories under dirname path*/
    public $exclude = [];

    /**
     * @var string $target
     * @var string $dirname
     * @var array<string> $files
     * @var array<string> $exclude
     */
    public function __construct(Config $config, string $target, string $watch, array $files, array $exclude = [])
    {
        $this->target = $target;
        $this->watch = $watch;
        $this->files = $files;
        $this->exclude = $exclude;
        $this->config = $config;
    }

    public function getTarget(string $filename = "")
    {
        $target = $this->prepare($this->target);
        $target = str_replace("{filename}", $filename, $this->target);
        return $target;
    }

    public function getDirname()
    {
        $dirname = $this->prepare($this->watch);
        return $dirname;
    }

    private function prepare(string $path)
    {
        if (preg_match_all("/\{([a-z])\}/", $path, $matches)) {
            if ($matches) {
                foreach ($matches[1] as $tag) {
                    $value = isset($this->config->{$tag}) ? $this->config->{$tag} : null;
                    if (!$value) {
                        error_log("unrecognized ($tag) property of compiler.config");
                        continue;
                    }

                    $path = str_replace("\{{$tag}\}", $value, $path);
                }
            }
        }

        return $path;
    }
}
