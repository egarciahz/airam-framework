<?php

namespace Airam\Commons\Compiler;

use Closure;
use Error;
use InvalidArgumentException;
use Opis\Closure\SerializableClosure;
use RuntimeException;

class Compiler
{

    public function compileArray(array $array): string
    {
        $code = array_map(function ($value, $key) {
            $compiledValue = $this->compileValue($value);
            $key = var_export($key, true);

            return "{$key} => {$compiledValue}";
        }, $array, array_keys($array));
        $code = join(',' . PHP_EOL, $code);

        return $code;
    }

    public function compileValue($value)
    {
        if ($value instanceof Closure) {
            return $this->compileClosure($value);
        }

        if (is_array($value)) {
            return $this->compileArray($value);
        }

        if (is_resource($value)) {
            throw new Error('An object was found but objects cannot be compiled', 500);
        }

        if (is_object($value)) {
            throw new Error('A resource was found but resources cannot be compiled', 500);
        }

        return var_export($value, true);
    }

    public function compileClosure(Closure $closure): string
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

    public function returnWrapper(string $code): string
    {
        $code = "return " . trim($code, "\t\n\r;") . ";";
        return $code;
    }

    /**
     * build a single php file whit the definitions
     * 
     * @param string $filename
     * @param string|null $namespace
     * @param array $usages
     * @param mixed $value
     * 
     * @return bool
     */
    public function compile(string $filename, string $namespace = null, array $usages = [], $value)
    {
        $code = $this->compileValue($value);
        return $this->returnWrapper($code);
    }

    private static function createDirectory(string $directory)
    {
        if (!is_dir($directory) && !@mkdir($directory, 0777, true)) {
            throw new InvalidArgumentException(sprintf('Compilation directory does not exist and cannot be created: %s.', $directory));
        }
        if (!is_writable($directory)) {
            throw new InvalidArgumentException(sprintf('Compilation directory is not writable: %s.', $directory));
        }
    }

    private static function writeFile(string $filename, $data)
    {
    }
}
