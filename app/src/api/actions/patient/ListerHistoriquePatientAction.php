<?php
declare(strict_types=1);

namespace toubilib\api\actions\patient;

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
use toubilib\core\application\usecases\ServicePatientInterface;

class ListerHistoriquePatientAction extends AbstractAction
{
    private ServicePatientInterface $service;

    public function __construct(ServicePatientInterface $service)
    {
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'] ?? '';
        if (!Uuid::isValid($id)) {
            throw new HttpBadRequestException($request, 'Identifiant patient invalide.');
        }

        try {
            $rdvs = $this->service->listerHistoriquePatient($id);
            $resources = array_map(fn($dto) => $this->rdvResource($request, $dto), $rdvs);
            $payload = [
                'data' => $resources,
                '_links' => $this->collectionLinks((string)$request->getUri()),
            ];

            return $this->respondWithJson($response, $payload);
        } catch (ResourceNotFoundException $exception) {
            throw new HttpNotFoundException($request, $exception->getMessage(), $exception);
        } catch (ValidationException $exception) {
            throw new HttpBadRequestException($request, $exception->getMessage(), $exception);
        } catch (Throwable $exception) {
            throw new HttpInternalServerErrorException($request, 'Une erreur interne est survenue.', $exception);
        }
    }
}
