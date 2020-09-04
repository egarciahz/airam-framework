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

class TemplateHandler implements MiddlewareInterface
{
    private $app;
    private $response;

    public function __construct(ContainerInterface $app, ResponseInterface $response)
    {
        $this->app = $app;
        $this->response = $response;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var RouterStatusInterface $status */
        $status = $request->getAttribute(Router::HANDLE_STATUS_CODE);
        if ($status->getStatus() !== StatusCode::HTTP_OK_CODE) {
            return $handler->handle($request);
        }

        /** @var callable|string|null $routeHandler */
        $routeHandler = $status->getHandler();

        if (!$routeHandler) {
            $router = new RouterStatus(StatusCode::HTTP_EXPECTATION_FAILED_CODE, $status->getUri());
            $request = $request->withAttribute(Router::HANDLE_STATUS_CODE, $status);
            return $handler->handle($request);
        }

        if (is_callable($routeHandler)) {
            $result = call_user_func($routeHandler, $request);
        } else if (class_exists($routeHandler)) {

            /** @var TemplateInterface $controller */
            $controller = $this->service->app()->get($routeHandler);

            if (is_template($routeHandler)) {

                /** @var TemplateEngine $renderer*/
                $renderer = $this->service->app()->get(TemplateEngine::class);
                $router = $this->service->app()->get(Router::HANDLE_MODULE_CODE);
                
                if ($layout = $router->getLayout()) {
                    /** @var LayoutInterface|null $layout */
                    $layout = $this->service->app()->get($layout);
                    $html  = $renderer->layout($layout, $controller);
                } else {
                    $html = $renderer->render($controller);
                }

                $result = new HtmlResponse($html);
            } else {

                $result = is_callable($controller) ? $controller($request) : StatusCode::getDescription(StatusCode::HTTP_EXPECTATION_FAILED_CODE);
            }
        }

        if ($result instanceof ResponseInterface) {
            return $result;
        }

        $this->response->getBody()->write($result);
        return $this->response;
    }
}
