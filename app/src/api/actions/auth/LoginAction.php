<?php
declare(strict_types=1);

namespace toubilib\api\actions\auth;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use toubilib\api\provider\AuthProviderInterface;
use toubilib\core\domain\exceptions\InvalidCredentialsException;
use toubilib\api\actions\AbstractAction;

class LoginAction extends AbstractAction
{
    private AuthProviderInterface $authProvider;

    public function __construct(AuthProviderInterface $authProvider)
    {
        $this->authProvider = $authProvider;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        try {
            $authTokens = $this->authProvider->signin($email, $password);
            
            $response->getBody()->write(json_encode($authTokens));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (InvalidCredentialsException $e) {
            $response->getBody()->write(json_encode(['error' => 'Identifiants invalides']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        } catch (\Exception $e) {
             $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
