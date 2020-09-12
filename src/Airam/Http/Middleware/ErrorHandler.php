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
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;

class ErrorHandler implements MiddlewareInterface
{
    /** 
     * @var callable $getEnviromentMode 
     */
    private $getEnviromentMode;

    public function __construct(callable $enviroment)
    {
        $this->getEnviromentMode = function () use ($enviroment): bool {
            return $enviroment();
        };
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var RouterStatusInterface $status */
        $status = $request->getAttribute(Router::HANDLE_STATUS_CODE);
        if (!$status) {
            throw new InvalidArgumentException("RouterStatus attribute request don't yet implemented");
        }

        $code = $status->getStatus();
        $method = $request->getMethod();
        $message = $status->getMessage(StatusCode::getMessage($code));
        $description = StatusCode::getDescription($code);
        $note = $status->getUri()->getPath();

        $isJSON = array_search("application/json", $request->getHeader("Accept"));
        if ($isJSON) {

            $response = new JsonResponse([
                "error" => [
                    "code" => $code,
                    "message" => $message,
                    "method" => $method
                ]
            ], $code);
        } else {

            ob_start();
            require __DIR__ . '/../Resources/errorTemplate.php';
            $html = ob_get_clean();
            $response = new HtmlResponse($html, $code);
        }

        if (StatusCode::HTTP_METHOD_NOT_ALLOWED_CODE === $code) {
            $response = $response->withHeader("Allow", join(",", $status->getParams()));
        }

        return $response;
    }
}
