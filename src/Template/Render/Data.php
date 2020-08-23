<?php

namespace Core\Template\Render;

use Core\Template\Layout;
use Core\Template\Template;

class Data
{
    public $name;
    public $namespace;
    public $properties = [];
    public $methods = [];

    public static function isTemplate($object): bool
    {
        return class_exists($object) ? (array_search(Template::class, class_uses($object)) !== false) : false;
    }

    public static function isLayout($object): bool
    {
        return self::isTemplate($object) && array_search(Layout::class, class_uses($object)) !== false;
    }
}
