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

    public function getParams()
    {
        return $this->status->getParams();
    }

    public function getQuery()
    {
        return $this->request->getQueryParams();
    }

    public function getData()
    {
        $this->request->getParsedBody();
    }

    public function getFiles()
    {
        $this->request->getUploadedFiles();
    }

    public function getCookies()
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
