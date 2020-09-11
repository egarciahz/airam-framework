<?php

namespace Airam\Template;

interface LayoutInterface extends TemplateInterface
{
    public function setYield(string $name, $hbs);
}
