<?php
declare(strict_types=1);

namespace toubilib\api\actions\praticien;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Ramsey\Uuid\Uuid;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpInternalServerErrorException;
use Slim\Exception\HttpNotFoundException;
use Throwable;
use toubilib\api\actions\AbstractAction;
use toubilib\core\application\exceptions\ResourceNotFoundException;
use toubilib\core\application\exceptions\ValidationException;
use toubilib\core\application\usecases\ServiceIndisponibiliteInterface;

class SupprimerIndisponibiliteAction extends AbstractAction
{
    private ServiceIndisponibiliteInterface $service;

    public function __construct(ServiceIndisponibiliteInterface $service)
    {
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $praticienId = $args['id'] ?? '';
        $indispoId = $args['indispoId'] ?? '';

        if (!Uuid::isValid($praticienId) || !Uuid::isValid($indispoId)) {
            throw new HttpBadRequestException($request, 'Identifiants invalides.');
        }

        try {
            $this->service->supprimerIndisponibilite($indispoId, $praticienId);
            return $response->withStatus(204);
        } catch (ResourceNotFoundException $exception) {
            throw new HttpNotFoundException($request, $exception->getMessage(), $exception);
        } catch (ValidationException $exception) {
            throw new HttpBadRequestException($request, $exception->getMessage(), $exception);
        } catch (Throwable $exception) {
            throw new HttpInternalServerErrorException($request, 'Une erreur interne est survenue.', $exception);
        }
    }
}
