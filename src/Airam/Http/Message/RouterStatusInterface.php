<?php

namespace Airam\Http\Message;

interface RouterStatusInterface
{
    /**
     * @return string|array|callable
     */
    public function getHandler();
    public function getParams(): array;
    public function getStatus(): int;
}
