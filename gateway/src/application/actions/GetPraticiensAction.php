<?php
declare(strict_types=1);

namespace toubilib\gateway\application\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use GuzzleHttp\Client;

class GetPraticiensAction
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $responseToubilib = $this->client->get('/praticiens');
        
        $response->getBody()->write($responseToubilib->getBody()->getContents());
        
        return $response->withHeader('Content-Type', 'application/json');
    }
}
