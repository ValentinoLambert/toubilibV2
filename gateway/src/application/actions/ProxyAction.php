<?php
declare(strict_types=1);

namespace toubilib\gateway\application\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class ProxyAction
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $path = $request->getUri()->getPath();
        $method = $request->getMethod();
        $options = [
            'headers' => $this->filterHeaders($request->getHeaders()),
        ];
        $query = $request->getQueryParams();
        if (!empty($query)) {
            $options['query'] = $query;
        }
        $body = (string)$request->getBody();
        if ($body !== '') {
            $options['body'] = $body;
        }
        
        try {
            $responseToubilib = $this->client->request($method, $path, $options);
            $response->getBody()->write($responseToubilib->getBody()->getContents());
            return $this->withUpstreamHeaders($response, $responseToubilib);
        } catch (ClientException $e) {
            $upstream = $e->getResponse();
            if ($upstream === null) {
                throw $e;
            }
            $response->getBody()->write($upstream->getBody()->getContents());
            return $this->withUpstreamHeaders($response, $upstream);
        }
    }

    /**
     * @param array<string, array<int, string>> $headers
     * @return array<string, array<int, string>>
     */
    private function filterHeaders(array $headers): array
    {
        $blocked = ['host', 'content-length'];
        $filtered = [];
        foreach ($headers as $name => $values) {
            if (in_array(strtolower($name), $blocked, true)) {
                continue;
            }
            $filtered[$name] = $values;
        }
        return $filtered;
    }

    private function withUpstreamHeaders(Response $response, Response $upstream): Response
    {
        $response = $response->withStatus($upstream->getStatusCode());
        $contentType = $upstream->getHeaderLine('Content-Type');
        if ($contentType !== '') {
            $response = $response->withHeader('Content-Type', $contentType);
        }
        $location = $upstream->getHeaderLine('Location');
        if ($location !== '') {
            $response = $response->withHeader('Location', $location);
        }
        return $response;
    }
}
