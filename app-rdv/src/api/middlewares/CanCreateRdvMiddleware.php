<?php
declare(strict_types=1);

namespace toubilib\api\middlewares;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpUnauthorizedException;
use toubilib\core\application\dto\InputRendezVousDTO;
use toubilib\core\application\dto\UserDTO;
use toubilib\core\application\exceptions\AuthorizationException;
use toubilib\core\application\exceptions\ResourceNotFoundException;
use toubilib\core\application\usecases\AuthorizationServiceInterface;

class CanCreateRdvMiddleware implements MiddlewareInterface
{
    private AuthorizationServiceInterface $authorizationService;

    public function __construct(AuthorizationServiceInterface $authorizationService)
    {
        $this->authorizationService = $authorizationService;
    }

    public function process(Request $request, Handler $handler): Response
    {
        $user = $request->getAttribute(AuthenticatedMiddleware::ATTRIBUTE_USER);
        if (!$user instanceof UserDTO) {
            throw new HttpUnauthorizedException($request, 'Authentification requise.');
        }

        $dto = $request->getAttribute(CreateRendezVousMiddleware::ATTRIBUTE_DTO);
        if (!$dto instanceof InputRendezVousDTO) {
            throw new HttpBadRequestException($request, 'Données de rendez-vous manquantes.');
        }

        try {
            $this->authorizationService->assertCanCreateRdv($user, $dto->patientId, $dto->praticienId);
        } catch (AuthorizationException $exception) {
            throw new HttpForbiddenException($request, $exception->getMessage(), $exception);
        } catch (ResourceNotFoundException $exception) {
            throw new HttpNotFoundException($request, $exception->getMessage(), $exception);
        }

        return $handler->handle($request);
    }
}
