<?php

namespace Airam\Compiler;

use function Airam\Commons\path_join;

class Config
{
    public $root;
    public $watch;
    public $subdirs;

    /**
     * @var string $dirname
     * @var string $watch
     * @var array<string> $subdirs
     */
    public function __construct(string $dirname, string $watch, array $subdirs = [])
    {
        $this->root = realpath(path_join(DIRECTORY_SEPARATOR, getenv("ROOT_DIR"), $dirname));
        $this->subdirs = $subdirs;
        $this->watch = $watch;
    }

    public static function fromArray(array $config): Config
    {
        return new Config($config["root"], $config["watch"], $config["subdirs"]);
    }

    public function build(){
        FileSystem::makeDirectoryMap($this->root, $this->subdirs);
    }
}
