<?php

namespace Core;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class FilesystemCache extends Filesystem
{
    /** @var string $rootPath */
    private $rootPath;
    private $struct;

    public function __construct()
    {
        $this->rootPath = getenv('ROOT_DIR');
        $this->struct = include "./config/cache.php";
    }

    public function getFile()
    {
    }

    public function getView()
    {
    }

    public function checkVersion()
    {
    }

    public function makeFile()
    {
    }

    public function __invoke()
    {
    }
}
