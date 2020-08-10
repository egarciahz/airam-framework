<?php

namespace Core\Http;

class Route
{
    public $method;
    public $path;
    public $handler;
    private $nested = false;

    /**
     * @param string|array $method Http method
     * @param string $path Route uri
     * @param string|array $handler string value used as controller, array its used for childrens
     */
    public function __construct($method, string $path, $handler)
    {
        $this->method = strtoupper($method);
        $this->path = $path;

        if (is_array($handler)) {
            $this->nested = true;
        }
    }

    public function isNested()
    {
        return $this->nested;
    }
}
