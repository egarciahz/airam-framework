<?php

namespace Airam\Template\Render;

use LightnCandy\Runtime;

class RuntimeProcessor extends Runtime
{
    static public function raw($cx, $v, $ex = 0)
    {
        return '[[DEBUG:raw()=>' . var_export($v, true) . ']]';
    }
}
