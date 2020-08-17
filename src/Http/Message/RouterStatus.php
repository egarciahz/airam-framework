<?php

namespace Core\Http\Message;

class RouterStatus implements RouterStatusInterface
{
    private $handler;
    private $params;
    private $status;

    public function __construct(int $status, array $params = null, string $handler = null)
    {
        $this->status = $status;
        $this->params = $params;
        $this->handler = $handler;
    }

    public function getHandler(): string
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
