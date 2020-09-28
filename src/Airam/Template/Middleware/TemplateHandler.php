<?php

namespace Airam\Template\Middleware;

use Airam\Http\Router;
use Airam\Http\Message\RouterStatus;
use Airam\Template\LayoutInterface;
use Airam\Template\Render\Engine as TemplateEngine;

use HttpStatusCodes\HttpStatusCodes as StatusCode;
use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function Airam\Template\Lib\{is_template};
use Closure;
use DI\Container;

class TemplateHandler implements MiddlewareInterface
{
    private $app;
    private $response;

    public function __construct(Container $app, ResponseInterface $response)
    {
        $this->app = $app;
        $this->response = $response;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var RouterStatus $status */
        $status = $request->getAttribute(Router::HANDLE_STATUS_CODE);
        if ($status->getStatus() !== StatusCode::HTTP_OK_CODE) {
            return $handler->handle($request);
        }

        /** @var Closure|resource $routeHandler */
        $routeHandler = $status->getHandler();
        $result = null;

        if (!$routeHandler) {
            $router = new RouterStatus(StatusCode::HTTP_EXPECTATION_FAILED_CODE, $status->getUri());
            $router->setMessage(StatusCode::getMessage(StatusCode::HTTP_EXPECTATION_FAILED_CODE));

            $request = $request->withAttribute(Router::HANDLE_STATUS_CODE, $status);
            return $handler->handle($request);
        }

        // register the request server data
        $this->app->set(ServerRequestInterface::class, $request);

        if ($routeHandler instanceof Closure || is_callable($routeHandler)) {
            $result =  $this->app->call($routeHandler,  ["request" => $request]);
        }

        if (is_template($routeHandler) && !$result) {

            /** @var TemplateEngine $renderer*/
            $renderer = $this->app->get(TemplateEngine::class);
            $router = $this->app->get(Router::HANDLE_MODULE_CODE);

            if ($layout = $router->getLayout()) {
                /** @var LayoutInterface|null $layout */
                $layout = $this->app->get($layout);
                $html  = $renderer->layout($layout, $routeHandler);
            } else {
                $html = $renderer->render($routeHandler);
            }

            $result = new HtmlResponse($html);
        }

        if (!$result) {
            $router = new RouterStatus(StatusCode::HTTP_EXPECTATION_FAILED_CODE, $status->getUri());
            $router->setMessage("Un-handled controller class type.");

            $request = $request->withAttribute(Router::HANDLE_STATUS_CODE, $status);
            return $handler->handle($request);
        }

        if ($result instanceof ResponseInterface) {
            return $result;
        }

        $this->response->getBody()->write($result);
        return $this->response;
    }
}
