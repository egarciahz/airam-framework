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

    private $message;

    /**
     * @param int $status
     * @param array $params
     * @param string|array<string> $handler
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

    public function getMessage(string $default = null): ?string
    {
        return ($this->message) ? $this->message : $default;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    public function toArray(): array
    {
        return [
            "uri" => (string) $this->uri,
            "message" => $this->message,
            "status" => $this->status,
            "params" => $this->params
        ];
    }
}
