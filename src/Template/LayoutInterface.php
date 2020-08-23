<?php

namespace Core\Template;

interface LayoutInterface extends TemplateInterface
{
    public function setYield(string $name, $hbs);
    public static function __isLayout(): bool;
}
