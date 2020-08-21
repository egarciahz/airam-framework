<?php

namespace Core\Template;
use Core\Template\Render\Data;

interface TemplateInterface
{
    public function __toRender(): Data;
    public static function __toBuild(): Data;
}
