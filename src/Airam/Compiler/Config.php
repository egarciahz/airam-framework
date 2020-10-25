<?php

namespace Airam\Compiler;

use function Airam\Commons\path_join;

class Config
{
    public $tmp;
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
        $this->root = path_join(DIRECTORY_SEPARATOR, getenv("ROOT_DIR"), $dirname);
        $this->watch = path_join(DIRECTORY_SEPARATOR, getenv("ROOT_DIR"), $watch);
        $this->tmp = path_join(DIRECTORY_SEPARATOR, $this->root, "tmp");
        $this->subdirs = $subdirs;
    }

    public static function fromArray(array $config): Config
    {
        return new Config($config["root"], $config["watch"], $config["subdirs"]);
    }

    public function build()
    {
        FileSystem::makeDirectoryMap($this->root, $this->subdirs);
        FileSystem::makeDirectory($this->tmp);
    }

    public function buildAt()
    {
        FileSystem::remove($this->root);
        $this->build();
    }
}
