<?php

namespace App\Http\Middlewares;

use App\Http\Exceptions\ApiException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ApiErrorHandlerMiddleware
{
    public function __construct(private ResponseFactoryInterface $responseFactory)
    {
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $requestHandler
     * @return ResponseInterface
     * @throws \JsonException
     */
    public function __invoke(ServerRequestInterface $request,
                             RequestHandlerInterface $requestHandler): ResponseInterface
    {
        try {
            return $requestHandler->handle($request);
        } catch (ApiException $e){
            $response = $this->responseFactory->createResponse($e->getCode());
            $response->withHeader('Content-type', 'application/json');

            $body = [
                'ok' => false,
                'error_code' => $e->getCode(),
                'description' => $e->getMessage()
            ];

            if ($e->hasErrorData()) {
                $body['error_data'] = $e->getErrorData();
            }

            $response->getBody()->write(json_encode($body, JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR));
            return $response;
        }
    }
}
