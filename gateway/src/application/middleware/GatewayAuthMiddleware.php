<?php
declare(strict_types=1);

namespace toubilib\gateway\application\middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Exception\HttpUnauthorizedException;
use Psr\Container\ContainerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class GatewayAuthMiddleware implements MiddlewareInterface
{
    private Client $authClient;

    public function __construct(ContainerInterface $container)
    {
        $this->authClient = $container->get('auth.client');
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        // 1. Récupérer le token du header Authorization
        $authHeader = $request->getHeaderLine('Authorization');
        
        if (empty($authHeader) || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            throw new HttpUnauthorizedException($request, "Token manquant ou mal formé");
        }
        
        $token = $matches[1];

        try {
            // 2. Appeler le microservice d'authentification pour valider le token
            $response = $this->authClient->get('/tokens/validate', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token
                ]
            ]);

            // 3.  Récupérer les infos utilisateur et les injecter dans la requête
            $userData = json_decode($response->getBody()->getContents(), true);
            $request = $request->withAttribute('user', $userData);

        } catch (ClientException $e) {
            // Le token est invalide (401 du service auth)
            throw new HttpUnauthorizedException($request, "Token invalide ou expiré", $e);
        } catch (\Exception $e) {
            // Erreur serveur ou autre connection refusée
            throw new HttpUnauthorizedException($request, "Impossible de valider le token", $e);
        }

        return $handler->handle($request);
    }
}
