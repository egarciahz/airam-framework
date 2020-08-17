<?php

namespace Core\Http\Message;

interface RouterStatusInterface
{
    public function getHandler(): string;
    public function getParams(): array;
    public function getStatus(): int;
}
