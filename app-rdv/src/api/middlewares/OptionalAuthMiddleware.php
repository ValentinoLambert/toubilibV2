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

class OptionalAuthMiddleware implements MiddlewareInterface
{
    private AuthProviderInterface $authProvider;

    public function __construct(AuthProviderInterface $authProvider)
    {
        $this->authProvider = $authProvider;
    }

    public function process(Request $request, Handler $handler): Response
    {
        $authorization = $request->getHeaderLine('Authorization');
        if (!$authorization) {
            return $handler->handle($request);
        }

        if (!preg_match('/^Bearer\s+(.*)$/i', $authorization, $matches)) {
            throw new HttpUnauthorizedException($request, 'Format du jeton invalide.');
        }

        $token = $matches[1];

        try {
            $user = $this->authProvider->authenticateAccessToken($token);
            $request = $request->withAttribute(AuthenticatedMiddleware::ATTRIBUTE_USER, $user);
        } catch (InvalidTokenException|UserNotFoundException $exception) {
            throw new HttpUnauthorizedException($request, 'Jeton invalide ou expiré.', $exception);
        }

        return $handler->handle($request);
    }
}
