<?php

namespace App\Services;

use Carbon\Carbon;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class LogMiddleware
{
    public static function debugRequest(LoggerInterface $logger, $formatter, string $logLevel = 'info'): callable
    {
        return static function (callable $handler) use ($logger, $formatter, $logLevel): callable {
            return static function (RequestInterface $request, array $options = []) use ($handler, $logger, $formatter, $logLevel) {
                $method = $request->getMethod();
                $requestDateTime = Carbon::now();
                $url = (string)$request->getUri();

                return $handler($request, $options)->then(
                    static function (Response $response) use ($logger, $url, $requestDateTime, $method): ResponseInterface {
                        $statusCode = $response->getStatusCode();
                        $responseBody = (string)$response->getBody();
                        $logger->debug('Request and response data', [
                            'method' => $method,
                            'made_at' => $requestDateTime->toIso8601String(),
                            'url' => $url,
                            'response_status_code' => $statusCode,
                            'response body' => $responseBody
                        ]);

                        return $response;
                    },
                );
            };
        };    }
}
