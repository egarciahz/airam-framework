<?php

namespace Airam\Template\Lib;

use Airam\Template\{Template, Layout};
use Airam\Template\Render\Renderable;
use Closure;

use function Airam\Commons\{
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

function is_template($ref)
{
    return class_use($ref, Template::class);
}

function is_layout($ref)
{
    return class_use($ref, Template::class) && class_use($ref, Layout::class);
}

function is_renderable($ref){
    return class_use($ref, Renderable::class);
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

function cleanFileName(string $path){
    return preg_replace("/\..+$/", "", basename($path));
}