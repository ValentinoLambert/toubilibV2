<?php
declare(strict_types=1);

namespace toubilib\api\actions\auth;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use toubilib\api\security\JwtManagerInterface;
use toubilib\api\security\InvalidTokenException;
use toubilib\api\actions\AbstractAction;

class ValidateTokenAction extends AbstractAction
{
    private JwtManagerInterface $jwtManager;

    public function __construct(JwtManagerInterface $jwtManager)
    {
        $this->jwtManager = $jwtManager;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        // Récupération du header Authorization
        $authHeader = $request->getHeaderLine('Authorization');
        if (empty($authHeader) || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
             $response->getBody()->write(json_encode(['error' => 'Token manquant']));
             return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }
        
        $token = $matches[1];

        try {
            // Décodage et validation du token
            $payload = $this->jwtManager->decode($token, 'access');
            
            // Si le token est valide, on renvoie les infos de l'utilisateur
            $response->getBody()->write(json_encode([
                'user_id' => $payload->subject ?? null,
                'email' => $payload->email ?? null,
                'role' => $payload->role ?? null
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (InvalidTokenException $e) {
            $response->getBody()->write(json_encode(['error' => 'Token invalide ou expiré']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        } catch (\Exception $e) {
             $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
