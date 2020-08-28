<?php

namespace Airam\Utils;

use Opis\Closure\SerializableClosure;
use Closure;

function randomId(int $length = 16): string
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $String = '';

    for ($i = 0; $i < $length; ++$i) {
        $String .= $characters[rand(0, $charactersLength - 1)];
    }

    return $String;
}

function path_join(string $separator, ...$args): string
{
    $paths = array();
    foreach ($args as $arg) {
        $paths = array_merge($paths, (array) $arg);
    }

    $paths = array_map(function ($p) use ($separator) {
        return trim($p,  $separator);
    }, $paths);

    $paths = array_filter($paths);
    $initial_char = is_array($args[0]) === true ? $args[0][0][0] : $args[0][0];

    return ($initial_char ===  $separator ? $separator : '') . join($separator, $paths);
}

function closureFactory(Closure $closure)
{
    $serializable = new SerializableClosure($closure);
    $code = $serializable->serialize();

    $code = preg_replace('/^(a|[\@\d]).*(\;s\:+[0-9])*\:"function./', "function", $code);
    $code = preg_replace('/(\"\;s\:+[0-9]*\:"scope.*)$/', "", $code);
    
    return $code;
}
