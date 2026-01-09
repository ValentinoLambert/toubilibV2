<?php
declare(strict_types=1);

namespace toubilib\api\middlewares;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpInternalServerErrorException;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpUnauthorizedException;
use Slim\Routing\RouteContext;
use toubilib\core\application\dto\UserDTO;
use toubilib\core\application\exceptions\AuthorizationException;
use toubilib\core\application\exceptions\ResourceNotFoundException;
use toubilib\core\application\usecases\AuthorizationServiceInterface;

class CanViewPatientHistoryMiddleware implements MiddlewareInterface
{
    private AuthorizationServiceInterface $authorizationService;

    public function __construct(AuthorizationServiceInterface $authorizationService)
    {
        $this->authorizationService = $authorizationService;
    }

    public function process(Request $request, Handler $handler): Response
    {
        $route = RouteContext::fromRequest($request)->getRoute();
        if ($route === null) {
            throw new HttpInternalServerErrorException($request, 'Route introuvable pour le contrôle d\'autorisation.');
        }

        $user = $request->getAttribute(AuthenticatedMiddleware::ATTRIBUTE_USER);
        if (!$user instanceof UserDTO) {
            throw new HttpUnauthorizedException($request, 'Authentification requise.');
        }

        try {
            $this->authorizationService->assertCanViewPatientHistory($user, $route->getArgument('id'));
        } catch (AuthorizationException $exception) {
            throw new HttpForbiddenException($request, $exception->getMessage(), $exception);
        } catch (ResourceNotFoundException $exception) {
            throw new HttpNotFoundException($request, $exception->getMessage(), $exception);
        }

        return $handler->handle($request);
    }
}
