<?php

namespace Airam\Http\Middleware;

use Airam\Http\Message\RouterStatus;
use Airam\Http\Router;

use Laminas\Uri\Uri;
use HttpStatusCodes\HttpStatusCodes as StatusCode;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionMethod;

class RouterHandler implements MiddlewareInterface
{
    /** @var Router $dispatcher */
    private $dispatcher;
    /** @var ContainerInterface $provider */
    private $provider;

    public function __construct(Router $dispatcher, ContainerInterface $provider)
    {
        $this->dispatcher = $dispatcher;
        $this->provider = $provider;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $uri = new Uri((string) $request->getUri());
        $match = $this->dispatcher->dispatch($request->getMethod(), $uri->getPath());

        switch ($match[0]) {
            case Router::NOT_FOUND:
                // ... 404 Not Found
                $router = new RouterStatus(StatusCode::HTTP_NOT_FOUND_CODE, $uri);

                break;
            case Router::METHOD_NOT_ALLOWED:
                // ... 405 Method Not Allowed
                $allowedMethods = $match[1];
                $router = new RouterStatus(StatusCode::HTTP_METHOD_NOT_ALLOWED_CODE, $uri, $allowedMethods, null);

                break;
            case Router::FOUND:
                // ... 200 OK FOUND
                $data = $this->getHandler(/*route handler */$match[1]);
                list($code, $controller, $message) = $data;
                $router = new RouterStatus(
                    $code, // status code
                    $uri, // current uri
                    $match[2], // route params
                    $controller // handler
                );

                if ($code !== 200) {
                    // CUSTOM MESSAGE
                    $router->setMessage($message);
                }

                break;
            default:
                $router = new RouterStatus(StatusCode::HTTP_IM_A_TEAPOT_CODE, $uri);
        }

        $request = $request->withAttribute(Router::HANDLE_STATUS_CODE, $router);
        return $handler->handle($request);
    }

    /**
     * this method handle and make for the current route controller
     * 
     * @param string|array $handler
     * 
     * @return array<int,null|resource|Closure,string> return an array of valid http code, handler and custom message
     */
    private function getHandler($handler)
    {
        if (gettype($handler) === "array") {

            if (!$this->provider->has($handler[0])) {
                return [StatusCode::HTTP_INTERNAL_SERVER_ERROR_CODE, null, "Class handler '{$handler[0]}' could not be found."];
            }

            $object = $this->provider->get($handler[0]);
            if (method_exists($object, $handler[1])) {
                $method = new ReflectionMethod($object, $handler[1]);
                $handler = $method->getClosure($object);
            } else {
                return [StatusCode::HTTP_INTERNAL_SERVER_ERROR_CODE, null, "Handler method '{$handler[1]}' from class '{$handler[0]}' does not exist."];
            }
        } else if (gettype($handler) === "string") {

            if (!$this->provider->has($handler)) {
                return [StatusCode::HTTP_INTERNAL_SERVER_ERROR_CODE, null, "Class handler '{$handler}' could not be found."];
            }

            $handler = $this->provider->get($handler);
        }

        return [StatusCode::HTTP_OK_CODE, $handler, StatusCode::getMessage(StatusCode::HTTP_OK_CODE)];
    }
}
