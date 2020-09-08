<?php

namespace Airam\Commons\Compiler;

use Opis\Closure\SerializableClosure;
use RuntimeException;
use Closure;
use Error;

class Compiler
{
    public static function compileArray(array $array): string
    {
        $code = array_map(function ($value, $key) {
            $compiledValue = static::compileValue($value);
            $key = var_export($key, true);

            return "{$key} => {$compiledValue}";
        }, $array, array_keys($array));
        $code = join(',' . PHP_EOL, $code);

        return $code;
    }

    public static function compileClosure(Closure $closure): string
    {
        $wrapper = new SerializableClosure($closure);
        $reflector = $wrapper->getReflector();

        if ($reflector->getUseVariables()) {
            throw new RuntimeException('Cannot compile closures which import variables using the `use` keyword');
        }

        if ($reflector->isBindingRequired() || $reflector->isScopeRequired()) {
            throw new RuntimeException('Cannot compile closures which use $this or self/static/parent references');
        }

        $code = ($reflector->isStatic() ? '' : 'static ') . $reflector->getCode();
        return $code;
    }

    public static function compileValue($value)
    {
        if ($value instanceof Closure) {
            return static::compileClosure($value);
        }

        if (is_array($value)) {
            return static::compileArray($value);
        }

        if (is_resource($value)) {
            throw new Error('An object was found but objects cannot be compiled', 500);
        }

        if (is_object($value)) {
            throw new Error('A resource was found but resources cannot be compiled', 500);
        }

        return var_export($value, true);
    }

    private static function returnWrapper(string $code): string
    {
        return sprintf("return %s;", trim($code, "\t\n\r;="));
    }

    public static function compile($value, bool $isRetornable = true)
    {
        $code = static::compileValue($value);
        return $isRetornable ? static::returnWrapper($code) : "{$code};";
    }

    /**
     * bundle a single php file whit the definitions
     * 
     * @param mixed $value
     * @param string $filename
     * @param string|null $namespace
     * @param array $usages
     * 
     */
    public static function bundle($value, string $path, string $namespace = null, array $usages = [])
    {

        $data = new DataTokens;
        $data->filename = $path;

        $data->namespaceName = $namespace;
        $data->usages = $usages;
        $data->code = static::compile($value);

        ob_start();
        require __DIR__ . '/Template.php';
        $data->code = ob_get_clean();

        return file_put_contents($path, join(PHP_EOL, array("<?php", $data->code, "?>")));
    }
}
