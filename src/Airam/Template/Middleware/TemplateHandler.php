<?php

namespace Airam\Template\Middleware;

use Airam\Http\Lib\RouterStatusInterface;
use Airam\Http\Router;
use Airam\Http\Message\RouterStatus;
use Airam\Service\ApplicationService;
use Airam\Template\LayoutInterface;
use Airam\Template\TemplateInterface;
use Airam\Template\Render\Engine as TemplateEngine;

use HttpStatusCodes\HttpStatusCodes as StatusCode;
use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function Airam\Template\Lib\{is_template};

class TemplateHandler implements MiddlewareInterface
{
    private $service;
    private $response;

    public function __construct(ApplicationService $service, ResponseInterface $response)
    {
        $this->service = $service;
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
                /** @var Router $router */
                $router = $this->service->app()->get(Router::class);

                /** @var TemplateEngine $renderer*/
                $renderer = $this->service->app()->get(TemplateEngine::class);

                $layout = $router->getLayout();
                /** @var LayoutInterface|null $layout */
                $layout = $this->service->app()->get($layout);

                $html = $layout ? $renderer->layout($layout, $controller) : $renderer->render($controller);
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
