<?php
declare(strict_types=1);

namespace toubilib\api\middlewares;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Exception\HttpUnauthorizedException;
use toubilib\api\provider\AuthProviderInterface;
use toubilib\api\security\InvalidTokenException;
use toubilib\core\domain\exceptions\UserNotFoundException;

class AuthenticatedMiddleware implements MiddlewareInterface
{
    public const ATTRIBUTE_USER = 'auth.user';

    private AuthProviderInterface $authProvider;

    public function __construct(AuthProviderInterface $authProvider)
    {
        $this->authProvider = $authProvider;
    }

    public function process(Request $request, Handler $handler): Response
    {
        $authorization = $request->getHeaderLine('Authorization');
        if (!$authorization || !preg_match('/^Bearer\s+(.*)$/i', $authorization, $matches)) {
            throw new HttpUnauthorizedException($request, 'Jeton d\'authentification requis.');
        }

        $token = $matches[1];

        try {
            $user = $this->authProvider->authenticateAccessToken($token);
        } catch (InvalidTokenException|UserNotFoundException $exception) {
            throw new HttpUnauthorizedException($request, 'Jeton invalide ou expiré.', $exception);
        }

        $request = $request->withAttribute(self::ATTRIBUTE_USER, $user);
        return $handler->handle($request);
    }
}
