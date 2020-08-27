<?php

namespace Airam\Utils;

use Iterator;
use ArrayAccess;

abstract class GenericCollection implements Iterator, ArrayAccess
{
    /** @var int $cursor */
    private $cursor;

    /** @var array $array */
    protected $array = array();

    public function __construct(array $initial = array())
    {
        $this->cursor = 0;
        $this->array = $initial;
    }

    public function current()
    {
        return $this->array[$this->cursor];
    }

    public function next()
    {
        ++$this->cursor;
    }

    public function key()
    {
        return $this->cursor;
    }

    public function valid()
    {
        return isset($this->array[$this->cursor]);
    }

    public function rewind()
    {
        $this->cursor = 0;
    }

    public function toArray()
    {
        return $this->array;
    }

    public function offsetExists($offset)
    {
        return isset($this->array[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->array[$offset]) ? $this->array[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->array[] = $value;
        } else {
            $this->array[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->array[$offset]);
    }
}
