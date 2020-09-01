<?php

namespace Airam\Http\Service;

use Airam\Http\Lib\Match;
use Airam\Http\Lib\RouterStatusInterface;
use Airam\Http\Router;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @Injectable
 */
class RouteService
{
    /**
     * @var ServerRequestInterface $request
     */
    private $request;

    /**
     * @var RouterStatusInterface $status
     */
    private $status;

    /**
     * @return array|null
     */
    public function getParams(): ?array
    {
        return $this->status->getParams();
    }

    /**
     * @return array|null
     */
    public function getQuery(): ?array
    {
        return $this->request->getQueryParams();
    }

    /**
     * @return null|array|object
     */
    public function getData()
    {
        $this->request->getParsedBody();
    }

    public function getFiles(): array
    {
        return $this->request->getUploadedFiles();
    }

    public function getCookies(): array
    {
        return $this->request->getCookieParams();
    }

    public function match(): Match
    {
        return new Match($this->request, $this->status);
    }

    public function __call($name, $arguments)
    {
        return $this->request->getAttribute($name);
    }

    public function __invoke(ServerRequestInterface &$request)
    {
        $this->request = $request;
        $this->status  = $request->getAttribute(Router::HANDLE_STATUS_CODE);
    }
}
