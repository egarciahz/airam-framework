<?php

namespace Core\Template;

use Core\Template\Render\Data;
use Core\Utils\Tools;

use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use Error;

/**
 * This trait expose methods for compile and prepare component metadata for the rendering.
 */
trait Template
{
    /**
     * This property provide data config for template name making.
     * @var array $template_file_conf
     */
    private static $template_file_conf = [
        "ext" => "template.html",
    ];

    /**
     * 
     * @var string|null $__template_name This property its runtime seting whit the template name path
     */
    private static $__template_name = null;

    /**
     * Generate array of type [name => value] from the propertyes
     * @param ReflectionClass &$reflection
     * @return array
     */
    private function __reflectPropertyes(ReflectionClass &$reflection)
    {
        $data = [];
        $propertyes = $reflection->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED);

        $blacklist = [
            "template_file_conf"
        ];

        foreach ($propertyes as $key => $prop) {
            $name = $prop->getName();
            if (false === array_search($name, $blacklist)) {
                $data[$name] = $prop->getValue($this);
            }
        }

        return $data;
    }

    /**
     * Generate array of type [(string) name => Closure] from the methods
     * @param ReflectionClass &$reflection
     * @return array
     */
    private function __reflectMethods(ReflectionClass &$reflection)
    {
        $data = [];
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_PROTECTED);

        $blacklist = [
            "__reflectPropertyes",
            "__reflectMethods",
            "__reflectDataFromSure",
            "__toRender",
            "__toString",
            "__invoke",
            "__construct"
        ];

        foreach ($methods as $key => $method) {
            $name = $method->getName();
            if (false === array_search($name, $blacklist)) {
                $data[$name] = $method->getClosure($this);
            }
        }

        return $data;
    }

    /**
     * Make an array of type [(string) name => array] from the metadata of the methods, the parameter information will be included in the form:
     * ```text
     *  [
     *      method => [
     *          [ param => [
     *              "type" => mixed, 
     *              "index" => int, 
     *              "required" => boolean,
     *              "default" => mixed|null
     *              ]
     *          ]
     *      ] 
     *  ]
     * ```
     * @param ReflectionClass &$reflection
     * @return array
     */
    private static function __reflectDataFromSure(ReflectionClass &$reflection)
    {
        $data = [];
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_PROTECTED);

        $blacklist = [
            "__reflectPropertyes",
            "__reflectMethods",
            "__reflectDataFromSure",
            "__toRender",
            "__toString",
            "__invoke",
            "__construct"
        ];

        foreach ($methods as $key => $method) {

            $name = $method->getName();
            if (false === array_search($name, $blacklist)) {

                $parameters = $method->getParameters();
                $mdata = [
                    "name" => $name,
                    "params" => []
                ];

                foreach ($parameters  as $key => $parameter) {
                    $mdata["params"][$parameter->getName()] = [
                        "type" => $parameter->getType(),
                        "index" => $parameter->getPosition(),
                        "required" => !$parameter->isOptional(),
                        "default" => $parameter->isOptional() ? $parameter->getDefaultValue() : null
                    ];
                }

                $data[] = $mdata;
            }
        }

        return $data;
    }


    public static function __toBuild(): Data
    {
        $reflection = new ReflectionClass(self::class);
        $methods = self::__reflectDataFromSure($reflection);

        $name = $reflection->getShortName();
        $filename = $reflection->getFileName();
        $namespace = $reflection->getNamespaceName();

        self::$__template_name = join(".", [$name, self::$template_file_conf['ext']]);
        self::$__template_name = Tools::path_join(DIRECTORY_SEPARATOR, dirname($filename), self::$__template_name);

        if (!file_exists(self::$__template_name)) {
            throw new Error("Not Found Template File $name from [$namespace] controler.", 500);
        }

        if (!is_readable(self::$__template_name)) {
            throw new Error("Template File [$name] doesn't us readable", 500);
        }

        $data = new Data;
        $data->name = self::$__template_name;
        $data->helpers = $methods;
        $data->namespace = $namespace;
        return $data;
    }

    public function __toRender(): Data
    {
        $reflection = new ReflectionClass($this);

        $buildData = self::__toBuild();

        $data = new Data;
        $data->name = $buildData->name;
        $data->namespace = $buildData->namespace;
        $data->properties = $this->__reflectPropertyes($reflection);
        $data->helpers =  $this->__reflectMethods($reflection);

        return $data;
    }
}
