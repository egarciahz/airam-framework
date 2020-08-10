<?php

namespace Core\Utils;

use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

class Tools
{
    static function randomId(int $length = 16): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $String = '';
        for ($i = 0; $i < $length; ++$i) {
            $String .= $characters[rand(0, $charactersLength - 1)];
        }

        return $String;
    }

    static function path_join(string $separator, ...$args): string
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

    static public function getClassPath($object): string
    {
        $reflection_class = new ReflectionClass($object);
        $filename = $reflection_class->getFilename();
        return $filename;
    }

    static public function getClassName($object): string
    {
        $reflection_class = new ReflectionClass($object);
        $name = $reflection_class->getName();
        $name = array_reverse(explode("\\", $name));
        return $name[0];
    }

    /**
     * @param object $object
     * 
     * @return array{name,filename,directory,methods,properties,ReflectionClass} 
     */
    static public function getClassInfo($object): array
    {
        $reflection_class = new ReflectionClass($object);
        $filename = $reflection_class->getFilename();
        $name = $reflection_class->getName();
        $name = array_reverse(explode("\\", $name));

        return [
            "name" => $name[0],
            "filename" =>  $filename,
            "directory" => dirname($filename),
            "real_name" => $reflection_class->getName(),
            "methods" => $reflection_class->getMethods(ReflectionMethod::IS_PUBLIC),
            "properties" => $reflection_class->getProperties(ReflectionProperty::IS_PUBLIC),
            "ReflectionClass" => $reflection_class
        ];
    }

    /**
     * @link https://www.php.net/manual/es/features.file-upload.multiple.php#118180
     */
    static function getFixedFilesArray(): array
    {
        $walker = function ($arr, $fileInfokey, callable $walker) {
            $ret = array();
            foreach ($arr as $k => $v) {
                if (is_array($v)) {
                    $ret[$k] = $walker($v, $fileInfokey, $walker);
                } else {
                    $ret[$k][$fileInfokey] = $v;
                }
            }
            return $ret;
        };

        $files = array();
        foreach ($_FILES as $name => $values) {
            // init for array_merge
            if (!isset($files[$name])) {
                $files[$name] = array();
            }
            if (!is_array($values['error'])) {
                // normal syntax
                $files[$name] = $values;
            } else {
                // html array feature
                foreach ($values as $fileInfoKey => $subArray) {
                    $files[$name] = array_replace_recursive($files[$name], $walker($subArray, $fileInfoKey, $walker));
                }
            }
        }

        return $files;
    }
}
