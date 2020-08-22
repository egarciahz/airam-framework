<?php

namespace Core\Template;

use LightnCandy\Runtime;

class TemplateProcessorRuntime extends Runtime
{
    static public function raw($cx, $v, $ex = 0)
    {
        return '[[DEBUG:raw()=>' . var_export($v, true) . ']]';
    }
}
