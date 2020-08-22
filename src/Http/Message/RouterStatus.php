<?php

namespace Core\Http\Message;

class RouterStatus implements RouterStatusInterface
{
    private $handler;
    private $params;
    private $status;

    /**
     * @param int $status
     * @param array $params
     * @param string|array|callable $handler
     */
    public function __construct(int $status, array $params = null, $handler = null)
    {
        $this->status = $status;
        $this->params = $params;
        $this->handler = $handler;
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
}
