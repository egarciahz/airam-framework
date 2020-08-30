<?php

namespace Airam\Http\Message;

use Airam\Http\Lib\RouterStatusInterface;
use Laminas\Uri\UriInterface;

class RouterStatus implements RouterStatusInterface
{
    private $handler;
    private $params;
    private $status;
    private $uri;

    /**
     * @param int $status
     * @param array $params
     * @param string|array|callable $handler
     * @param UriInterface $uri
     */
    public function __construct(int $status, UriInterface $uri, array $params = null, $handler = null)
    {
        $this->status = $status;
        $this->params = $params;
        $this->handler = $handler;
        $this->uri = $uri;
    }

    public function getHandler()
    {
        return $this->handler;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getUri(): UriInterface
    {
        return $this->uri;
    }
}
