<?php
declare(strict_types=1);

namespace toubilib\api\middlewares;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Exception\HttpUnauthorizedException;
use toubilib\core\application\dto\UserDTO;

class AuthenticatedMiddleware implements MiddlewareInterface
{
    public const ATTRIBUTE_USER = 'auth.user';

    public function process(Request $request, Handler $handler): Response
    {
        $authorization = $request->getHeaderLine('Authorization');
        if (!$authorization || !preg_match('/^Bearer\s+(.*)$/i', $authorization, $matches)) {
            throw new HttpUnauthorizedException($request, 'Jeton d\'authentification requis.');
        }

        $token = $matches[1];

        $payload = $this->decodeTokenPayload($request, $token);
        $user = new UserDTO(
            (string)$payload['sub'],
            (string)($payload['email'] ?? ''),
            (int)$payload['role']
        );

        $request = $request->withAttribute(self::ATTRIBUTE_USER, $user);
        return $handler->handle($request);
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeTokenPayload(Request $request, string $token): array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new HttpUnauthorizedException($request, 'Jeton invalide.');
        }

        $payloadJson = $this->base64UrlDecode($parts[1]);
        if ($payloadJson === null) {
            throw new HttpUnauthorizedException($request, 'Jeton invalide.');
        }

        $payload = json_decode($payloadJson, true);
        if (!is_array($payload) || !isset($payload['sub']) || !isset($payload['role'])) {
            throw new HttpUnauthorizedException($request, 'Jeton invalide.');
        }

        return $payload;
    }

    private function base64UrlDecode(string $value): ?string
    {
        $value = strtr($value, '-_', '+/');
        $pad = strlen($value) % 4;
        if ($pad > 0) {
            $value .= str_repeat('=', 4 - $pad);
        }
        $decoded = base64_decode($value, true);
        return $decoded === false ? null : $decoded;
    }
}
