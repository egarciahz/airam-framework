<?php

namespace Core\Template;

interface LayoutInterface extends TemplateInterface
{
    public function setYield(string $name, $hbs);
}
