<?php

namespace Core\Utils;

use IteratorAggregate;
use ArrayIterator;

abstract class Collection implements IteratorAggregate
{
    protected $values = array();

    public function toArray(): array
    {
        return $this->values;
    }

    public function getIterator()
    {
        return new ArrayIterator($this->values);
    }
}
