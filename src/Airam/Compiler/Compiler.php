<?php

namespace Airam\Compiler;

use Opis\Closure\SerializableClosure;
use RuntimeException;
use Closure;
use Error;
use Exception;

class Compiler
{
    /**
     * @return array<string,DirMap>
     */
    static public function buildMaps(array $scheme)
    {
        $keys = array_keys($scheme);
        unset($keys[0]);

        $maps = array_map(function ($name) use ($scheme) {
            try {
                return DirMap::fromSchema($scheme, trim($name));
            } catch (Exception $error) {
                $message = sprintf("Durin '%s' rule compilation, %s", $name,  $error->getMessage());
                throw new RuntimeException($message, 0, $error);
            }
        }, $keys);

        return array_combine($keys, $maps);
    }

    public static function compileArray(array $array): string
    {
        $code = array_map(function ($value, $key) {
            $compiledValue = static::compileValue($value);
            $key = var_export($key, true);

            return "{$key} => {$compiledValue}";
        }, $array, array_keys($array));
        $code = join(PHP_EOL, ["array(" . join(',' . PHP_EOL, $code) . ")"]);

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

    public static function returnWrapper(string $code): string
    {
        return sprintf("return %s;", trim($code, "\t\n\r;="));
    }

    public static function wrap(string $code, bool $isRetornable = true): string
    {
        return join(PHP_EOL, array("<?php", ($isRetornable ? static::returnWrapper($code) : $code), "?>"));
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
     * @return bool|int
     */
    public static function bundle($value, string $path, string $namespace = null, array $usages = [], bool $isRawValue = false)
    {

        $data = new DataTokens;
        $data->filename = $path;

        $data->namespaceName = $namespace;
        $data->usages = $usages;
        $data->code = $isRawValue ? $value : static::compile($value);

        ob_start();
        require __DIR__ . '/Template.php';
        $data->code = ob_get_clean();

        $result = FileSystem::write($path, static::wrap($data->code, false));
        $result ?: error_log(sprintf("Unespected error ocurrent while compiling: %s\n", $path));

        return $result;
    }
}
