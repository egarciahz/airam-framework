<?php

namespace Airam\Template;
use Airam\Template\Render\Data;

interface TemplateInterface
{
    public function __toRender(): Data;
    public static function __toBuild(): Data;
}
