<?php
declare(strict_types=1);

namespace toubilib\gateway\application\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Slim\Exception\HttpNotFoundException;

class ProxyPraticiensAction
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $path = $request->getUri()->getPath();

        try {
            $responseToubilib = $this->client->get($path);
            $response->getBody()->write($responseToubilib->getBody()->getContents());
            return $response
                ->withStatus($responseToubilib->getStatusCode())
                ->withHeader('Content-Type', 'application/json');
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() === 404) {
                throw new HttpNotFoundException($request);
            }
            throw $e;
        }
    }
}
