<?php
declare(strict_types=1);

namespace toubilib\gateway\application\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class GetPraticienAction
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        
        try {
            $responseToubilib = $this->client->get("/praticiens/{$id}");
            $response->getBody()->write($responseToubilib->getBody()->getContents());
            return $response
                ->withStatus($responseToubilib->getStatusCode())
                ->withHeader('Content-Type', 'application/json');
        } catch (ClientException $e) {
            $response->getBody()->write($e->getResponse()->getBody()->getContents());
            return $response
                ->withStatus($e->getResponse()->getStatusCode())
                ->withHeader('Content-Type', 'application/json');
        }
    }
}
