<?php

namespace Core\Template;

trait Layout
{
    private $__yield_scopes = [];

    protected function Yield($name)
    {
        if (gettype($name) !== "string") {
            $name = "main";
        }
        return array_key_exists($name, $this->__yield_scopes) ? $this->__yield_scopes[$name] : null;
    }

    public function setYield(string $name, $hbs)
    {
        $this->__yield_scopes[$name] = $hbs;
    }
}
