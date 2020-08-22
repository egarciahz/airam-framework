<?php

namespace Core\Template\Middleware;

use Core\Application;
use Core\Http\Message\RouterStatus;
use Core\Http\Message\RouterStatusInterface;
use Core\Template\Template;
use Core\Template\TemplateInterface;

use HttpStatusCodes\HttpStatusCodes as StatusCode;
use LightnCandy\LightnCandy;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

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

        /** @var ResponseInterface $response */
        $response = ($this->responseFactory)();
        $handler = $status->getHandler();
        
        if (is_callable($handler)) {
            if (($res = $handler($request)) instanceof ResponseInterface) {
                return $res;
            }

            $response->getBody()->write($res);
            return $response;
        }

        if (class_exists($handler)) {
            $isTemplate = array_search(Template::class, class_uses($handler)) !== false;
            if ($isTemplate) {
                /** @var TemplateInterface $controller */
                $controller = new $handler;
                $result = $controller->__toRender();
                $compiled = LightnCandy::compile(file_get_contents($result->name), [
                    "flags" =>
                    LightnCandy::FLAG_RENDER_DEBUG |
                        LightnCandy::FLAG_BESTPERFORMANCE |
                        LightnCandy::FLAG_HANDLEBARSJS |
                        LightnCandy::FLAG_INSTANCE |
                        LightnCandy::FLAG_ADVARNAME,
                ]);

                $renderer = LightnCandy::prepare($compiled);
                $html = $renderer($controller);

                $response->getBody()->write($html);
            } else {
                $response = $response->withStatus(500, "Dont use Template");
                $response->getBody()->write("Dont use Template");
            }
        }

        return $response;
    }
}
