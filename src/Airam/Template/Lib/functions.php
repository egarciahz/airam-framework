<?php

namespace Airam\Template\Lib;

use Airam\Template\{Template, Layout};

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use Closure;

use function Airam\Utils\{
    path_join,
    class_use,
    closureFactory
};
use function str_replace;

function makeTemplateFileName(string $origin)
{
    $name = preg_replace("/^([a-z\\" . DIRECTORY_SEPARATOR . "]).+app\\" . DIRECTORY_SEPARATOR . "/", "", $origin);
    $name = str_replace(DIRECTORY_SEPARATOR, "_", $name);
    $name = preg_replace("/\..+$/", ".php", $name);
    return $name;
}

function matchFilesByExtension(string $folder, array $extensions, array $ignoreDirs = [], ?array $fileList = [])
{
    $dir = new RecursiveDirectoryIterator($folder);
    $iter = new RecursiveIteratorIterator($dir);
    $files = new RegexIterator($iter, '/(' . path_join("|", $extensions) . ')$/');
    $files->setMode(RegexIterator::MATCH);

    $exclude =  "({$folder}" . DIRECTORY_SEPARATOR . "+)*(" . path_join("|", $ignoreDirs) . ")";
    $exclude = "/^" . str_replace(DIRECTORY_SEPARATOR, "\\" . DIRECTORY_SEPARATOR, $exclude) . ".+$/";

    foreach ($files as $file) {
        $path = $file->getPathname();
        if (count($ignoreDirs) > 0 && preg_match($exclude, $path) !== 0) {
            continue;
        }
        $fileList[] = $path;
    }

    return $fileList;
}

function is_template($ref)
{
    return class_use($ref, Template::class);
}

function is_layout($ref)
{
    return class_use($ref, Template::class) && class_use($ref, Layout::class);
}

function closureCodeCompiler(Closure $closure, string $name)
{
    if ($closure instanceof Closure) {
        $code = closureFactory($closure);
        return " \"{$name}\" => {$code}";
    }
    return " \"{$name}\" => \"passport\" ";
}

/**
 * neutral element of render
 */
function passport()
{
    return null;
}

/**
 * eval php file wrapper
 * @param string $path absolute file path
 */
function loadResource(string $path)
{
    if (!file_exists($path)) {
        return false;
    }

    $result = require $path;
    return $result;
}
