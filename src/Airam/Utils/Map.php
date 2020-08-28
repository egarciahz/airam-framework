<?php

namespace Airam\Utils;

use Airam\Utils\Collection;

class Map extends Collection
{
    function __construct(array $value = null)
    {
        $this->values = $value || array();
    }

    function has(string $property)
    {
        return isset($this->values[$property]);
    }

    function set(string $property, $value)
    {
        return $this->values["{$property}"] = $value;
    }

    function get(string $property)
    {
        if (isset($this->values[$property])) {
            return $this->values[$property];
        };

        return null;
    }

    function length()
    {
        return count($this->values);
    }

    function clear()
    {
        $this->values = array();
    }
}
