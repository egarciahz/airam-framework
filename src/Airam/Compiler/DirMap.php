<?php

namespace Airam\Compiler;

use RuntimeException;

class DirMap
{
    /** @var Config $config compiler configuration*/
    private $config;

    /** @var string $target path to compiled file*/
    private $target;

    /** @var string $watch path to observable folder*/
    private $watch;

    /** @var array<string> $files list of available file extensions*/
    public $files = [];

    /** @var array<string> $exclude exclude directories under watchable path*/
    public $exclude = [];

    /**
     * @var string $target
     * @var string $dirname
     * @var array<string> $files
     * @var array<string> $exclude
     */
    public function __construct(Config $config, string $target, ?array $files, ?string $watch, array $exclude = [])
    {
        $this->config = $config;

        $this->watch =  $this->prepare($watch);
        $this->target = $target;
        $this->files = $files;
        $this->exclude = $exclude;
    }

    public static function fromSchema(array $schema, string $target): DirMap
    {
        $config = Config::fromArray($schema["config"]);
        $map = $schema[$target];
        $watch = $map["watch"];

        if ($watch) {
            return new DirMap($config, $map["target"], $watch["files"], $watch["dirname"], $watch["exclude"]);
        }

        return new DirMap($config, $map["target"], null, null);
    }

    public function getPath(string $name = null): string
    {
        return $this->prepare($this->target, ["filename" => $name]);
    }

    public function isFileExist(string $name = null): bool
    {
        return file_exists($this->getPath($name));
    }

    public function getDirname(): string
    {
        return $this->watch;
    }

    private function prepare(?string $path, array $scope = []): ?string
    {
        if (!$path) {
            return null;
        }

        $matches = [];
        if (preg_match_all("/\{*([a-z]+)\}/", $path, $matches)) {
            if ($matches) {
                foreach ($matches[1] as $tag) {
                    $value = isset($this->config->{$tag}) ? $this->config->{$tag} : ($scope[$tag] ?: false);
                    if ($value === false) {
                        throw new RuntimeException(sprintf("Unrecognized (%s) property of compiler['config'] ", $tag));
                    }

                    $path = str_replace("{{$tag}}", $value, $path);
                }
            }
        }

        return $path;
    }

    public function build()
    {
        $target = $this->prepare(dirname($this->target));
        FileSystem::makeDirectory($target);
    }
}
