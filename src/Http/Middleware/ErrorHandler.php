<?php

namespace Core\Http\Middleware;

use Core\Http\Message\RouterStatus;
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

    private $isDevelopmentMode;

    public function __construct(callable $responseFactory, bool $isDevelopmentMode = false)
    {
        $this->isDevelopmentMode = $isDevelopmentMode;
        $this->responseFactory = function () use ($responseFactory): ResponseInterface {
            return $responseFactory();
        };
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var ResponseInterface $response */
        $response = ($this->responseFactory)();

        /** @var RouterStatus|null $router */
        $router = $request->getAttribute(RouterHandler::class);
        if (!$router) {
            $classname = RouterHandler::class;
            throw new InvalidArgumentException("Attribute request with {$classname} not found.");
        }

        $response = $response->withStatus($router->getStatus());
        if (StatusCode::HTTP_METHOD_NOT_ALLOWED_CODE === $router->getStatus()) {
            $response = $response->withHeader("Allow", join(",", $router->getParams()));
        }

        $response->getBody()->write(sprintf(
            '<h3>Error %d</h3> <p> [<b>%s</b> <i>%s</i>] <br> %s </p>',
            $router->getStatus(),
            $request->getMethod(),
            (string) $request->getUri(),
            StatusCode::getMessage($router->getStatus())
        ));

        return $response;
    }
}
