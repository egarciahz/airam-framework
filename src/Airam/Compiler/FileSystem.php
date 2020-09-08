<?php

namespace Airam\Commons\Compiler;

use InvalidArgumentException;

use function Airam\Commons\path_join;

class FileSystem
{
    /** @var DataTokens|DirMap $scope */
    private $scope;

    /** @var string $root */
    private $root;

    /** @var string $base */
    private $base;

    /**
     * @param string $root 
     * @param DataTokens|DirMap $scope
     */
    public function __construct(string $root, $scope = null)
    {
        if (!$scope instanceof DataTokens && !$scope instanceof DirMap) {
            throw new InvalidArgumentException("scope must be instance of DataTokens or DirMap");
        }

        $this->root = $root;
        $this->base = $root;
        $this->scope = $scope;
    }

    public function save(?string $filename = null)
    {
        if ($this->scope instanceof DataTokens) {
        }
    }

    public function make()
    {
        $data = $this->scope;
        if ($data instanceof DataTokens) {
            $data = $this->scope->dirMap;
        }

        /** @var DirMap $data */
        if ($data) {
            $this->base = path_join(DIRECTORY_SEPARATOR, $this->root, $data->getDirname());
            $this->makeDirectoryMap($this->base, $data->subdirs);
        }
        return $this;
    }

    private function makeDirectoryMap(string $base, array $subdirs)
    {
        static::makeDirectory($base);
        foreach ($subdirs as $sub) {
            $sub =  realpath(path_join(DIRECTORY_SEPARATOR, $base, $sub));
            static::makeDirectory($sub);
        }
    }

    public static function makeDirectory(string $directory)
    {
        if (!is_dir($directory) && !@mkdir($directory, 0777, true)) {
            throw new InvalidArgumentException(sprintf('Compilation directory does not exist and cannot be created: %s.', $directory));
        }
        if (!is_writable($directory)) {
            throw new InvalidArgumentException(sprintf('Compilation directory is not writable: %s.', $directory));
        }
    }

    public static function writeFile(string $filename, string $content)
    {
    }
}
