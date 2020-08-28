<?php

namespace Airam\Template\Lib;

use Airam\Template\{Template, Layout};

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use RegexIterator;

use function Airam\Utils\closureFactory;
use function Airam\Utils\path_join;
use function str_replace;

function makeTemplateFileName(string $origin)
{
    $name = preg_replace("/^([a-z\\" . DIRECTORY_SEPARATOR . "]).+app\\" . DIRECTORY_SEPARATOR . "/", "", $origin);
    $name = str_replace(DIRECTORY_SEPARATOR, "_", $name);
    $name = preg_replace("/\..+$/", ".php", $name);
    return $name;
}

function matchFilesByExtension(string $folder, array $extensions, array $fileList = [])
{
    $dir = new RecursiveDirectoryIterator($folder);
    $iter = new RecursiveIteratorIterator($dir);
    $files = new RegexIterator($iter, '/(' . path_join("|", $extensions) . ')$/');
    $files->setMode(RegexIterator::MATCH);

    foreach ($files as $file) {
        $fileList[] = $file->getPathname();
    }

    return $fileList;
}

function class_use($target, $trait): bool
{
    $traits = [];
    if (gettype($target) === "string") {
        $traits = class_exists($target) ? class_uses($target) : "";
    } else {
        $ref = new ReflectionClass($target);
        $traits = $ref->getTraitNames();
    }

    return  array_search($trait, $traits) !== false;
}

function is_template($ref)
{
    return class_use($ref, Template::class);
}

function is_layout($ref)
{
    return class_use($ref, Template::class) && class_use($ref, Layout::class);
}
