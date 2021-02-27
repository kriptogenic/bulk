<?php

declare(strict_types=1);

namespace App\Http\Middlewares;

use App\Http\Exceptions\InvalidJsonInput;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class JsonBodyParserMiddleware implements MiddlewareInterface
{
    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws InvalidJsonInput
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $body = $request->getBody()->getContents();

        try {
            $contents = json_decode($body, flags: JSON_THROW_ON_ERROR | JSON_OBJECT_AS_ARRAY);
        } catch (\JsonException) {
            throw new InvalidJsonInput();
        }

        $request = $request->withParsedBody($contents);
        return $handler->handle($request);
    }
}
