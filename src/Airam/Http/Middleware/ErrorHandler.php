<?php

namespace Airam\Http\Middleware;

use Airam\Http\Router;
use Airam\Http\Lib\RouterStatusInterface;
use HttpStatusCodes\HttpStatusCodes as StatusCode;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use InvalidArgumentException;

class ErrorHandler implements MiddlewareInterface
{
    /** 
     * @var callable $responseFactory 
     */
    private $responseFactory;
    /**
     * @var bool $isDevMode
     */
    private $isDevMode;

    public function __construct(callable $responseFactory, bool $isDevMode = true)
    {
        $this->isDevMode = $isDevMode;
        $this->responseFactory = function () use ($responseFactory): ResponseInterface {
            return $responseFactory();
        };
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var ResponseInterface $response */
        $response = ($this->responseFactory)();

        /** @var RouterStatusInterface $status */
        $status = $request->getAttribute(Router::HANDLE_STATUS_CODE);
        if(!$status){
            throw new InvalidArgumentException("RouterStatus attribute request don't yet implemented");
        }

        $code = $status->getStatus();
        $response = $response->withStatus($code);
        if (StatusCode::HTTP_METHOD_NOT_ALLOWED_CODE === $code) {
            $response = $response->withHeader("Allow", join(",", $status->getParams()));
        }

        $response->getBody()->write(sprintf(
            '<h3>Error %d</h3> <p> [<b>%s</b> <i>%s</i>] <br> %s </p>',
            $status->getStatus(),
            $request->getMethod(),
            (string) $status->getUri(),
            StatusCode::getMessage($code)
        ));

        return $response;
    }
}
