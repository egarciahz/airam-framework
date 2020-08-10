<?php

namespace Core\Http;

use ArrayAccess;
use Iterator;
use Closure;

interface RouterInterface  extends Iterator, ArrayAccess
{
    public function current(): Route;
    public function offsetGet($offset): ?Route;

    public function isGroup(): bool;
    public function childrens(): ?RouterInterface;
    
    static public function parse(?RouterInterface $r): ?Closure;
}
