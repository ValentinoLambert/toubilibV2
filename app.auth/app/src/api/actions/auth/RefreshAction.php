<?php
declare(strict_types=1);

namespace toubilib\api\actions\auth;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use toubilib\core\application\usecases\ServiceAuthInterface;
use toubilib\api\security\JwtManagerInterface;
use toubilib\api\security\InvalidTokenException;
use toubilib\api\actions\AbstractAction;

class RefreshAction extends AbstractAction
{
    private ServiceAuthInterface $authService;
    private JwtManagerInterface $jwtManager;

    public function __construct(ServiceAuthInterface $authService, JwtManagerInterface $jwtManager)
    {
        $this->authService = $authService;
        $this->jwtManager = $jwtManager;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $authHeader = $request->getHeaderLine('Authorization');
        if (empty($authHeader) || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
             $response->getBody()->write(json_encode(['error' => 'Token manquant']));
             return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }
        
        $token = $matches[1];

        try {
            // Validate refresh token
            $payload = $this->jwtManager->decode($token); // Assuming decode validates signature and expiration
            
            // In a real scenario, we might want to verify 'scope' is 'refresh' if we differentiated them
             
            // Get user to generate new tokens
            // Assuming payload->uid or subject contains user ID
            $userId = $payload->sub ?? null;
            if (!$userId) {
                throw new InvalidTokenException("Invalid payload");
            }
            
            $userDTO = $this->authService->getUserById($userId); // Need to verify if this method exists in interface

            $newAccessToken = $this->jwtManager->createAccessToken($userDTO);
            $newRefreshToken = $this->jwtManager->createRefreshToken($userDTO);

            $response->getBody()->write(json_encode([
                'access_token' => $newAccessToken,
                'refresh_token' => $newRefreshToken
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
