<?php

namespace Core\Template;

trait Template
{
    static public function render()
    {
    }

    static public function getTemplateComponentDir()
    {
        return __DIR__ . DIRECTORY_SEPARATOR . __FILE__ . "::class " . __CLASS__;
    }
}
