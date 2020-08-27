<?php

namespace Airam\Template\Middleware;

use Airam\Application;
use Airam\Http\Router;
use Airam\Http\Message\RouterStatus;
use Airam\Http\Message\RouterStatusInterface;
use Airam\Template\LayoutInterface;
use Airam\Template\Render\Data;
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
    private $app;
    private $responseFactory;

    public function __construct(Application $app, ?callable $responseFactory)
    {
        $this->app = $app;
        $this->responseFactory = function () use ($responseFactory): ResponseInterface {
            return $responseFactory();
        };
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var RouterStatus $status */
        $status = $request->getAttribute(RouterStatusInterface::class);
        if ($status->getStatus() !== StatusCode::HTTP_OK_CODE) {
            return $handler->handle($request);
        }

        $missing_response_error = StatusCode::getDescription(StatusCode::HTTP_EXPECTATION_FAILED_CODE);

        /** @var ResponseInterface $response */
        $response = ($this->responseFactory)();

        /** @var callable|string|null $routeHandler */
        $routeHandler = $status->getHandler();

        if (!$routeHandler) {
            $status = 
            $request = $request->withAttribute(RouterStatusInterface::class, $status);
            return $handler->handle($request);
        }

        if (is_callable($routeHandler)) {
            $result = call_user_func($routeHandler, $request);
        } else if (class_exists($routeHandler)) {

            /** @var TemplateInterface $controller */
            $controller = $this->app->get($routeHandler);

            if (is_template($routeHandler)) {
                /** @var Router $router */
                $router = $this->app->get(Router::class);

                /** @var TemplateEngine $templating*/
                $templating = $this->app->get(TemplateEngine::class);

                /** @var LayoutInterface|null $controller */
                $layout = $router->getLayout();

                $html = $layout ? $templating->layout($layout, $controller) : $templating->render($controller);
                $result = new HtmlResponse($html, 200);
            } else {

                $result = is_callable($controller) ? $controller($request) : $missing_response_error;
            }
        }

        if ($result instanceof ResponseInterface) {
            return $result;
        }

        $response->getBody()->write($result);
        return $response;
    }
}
