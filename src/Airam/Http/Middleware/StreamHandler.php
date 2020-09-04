<?php

namespace Airam\Http\Middleware;

use Airam\Http\Router;
use Airam\Http\Lib\RouterStatusInterface;
use Airam\Commons\ApplicationService;

use HttpStatusCodes\HttpStatusCodes as StatusCode;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use RuntimeException;

class StreamHandler implements MiddlewareInterface
{
    /** @var ApplicationService $service */
    private $service;

    public function __construct(ApplicationService $service)
    {
        $this->service = $service;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var RouterStatusInterface $status */
        $status = $request->getAttribute(Router::HANDLE_STATUS_CODE);

        if (!$status) {
            throw new RuntimeException("RouterStatus attribute request don't yet implemented");
        }

        if ($status->getStatus() !== StatusCode::HTTP_OK_CODE) {
            return $handler->handle($request);
        }

        return $this->service->run($request, $handler);
    }
}
