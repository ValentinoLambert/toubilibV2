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
use toubilib\core\application\dto\UserDTO;
use toubilib\core\domain\exceptions\UserNotFoundException;
use toubilib\core\domain\entities\user\UserRole;

class AuthenticatedMiddleware implements MiddlewareInterface
{
    public const ATTRIBUTE_USER = 'auth.user';

    private AuthProviderInterface $authProvider;
    private string $internalToken;

    public function __construct(AuthProviderInterface $authProvider, string $internalToken)
    {
        $this->authProvider = $authProvider;
        $this->internalToken = $internalToken;
    }

    public function process(Request $request, Handler $handler): Response
    {
        $internal = $request->getHeaderLine('X-Internal-Token');
        if ($this->internalToken !== '' && hash_equals($this->internalToken, $internal)) {
            $serviceUser = new UserDTO('service-rdv', 'service@toubilib', UserRole::ADMIN);
            $request = $request->withAttribute(self::ATTRIBUTE_USER, $serviceUser);
            return $handler->handle($request);
        }

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
