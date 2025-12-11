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
use toubilib\api\middlewares\CreateIndisponibiliteMiddleware;
use toubilib\core\application\dto\InputIndisponibiliteDTO;
use toubilib\core\application\exceptions\ResourceNotFoundException;
use toubilib\core\application\exceptions\ValidationException;
use toubilib\core\application\usecases\ServiceIndisponibiliteInterface;

class CreerIndisponibiliteAction extends AbstractAction
{
    private ServiceIndisponibiliteInterface $service;

    public function __construct(ServiceIndisponibiliteInterface $service)
    {
        $this->service = $service;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $praticienId = $args['id'] ?? '';
        if (!Uuid::isValid($praticienId)) {
            throw new HttpBadRequestException($request, 'Identifiant praticien invalide.');
        }

        $payload = $request->getAttribute(CreateIndisponibiliteMiddleware::ATTRIBUTE_PAYLOAD);
        if (!is_array($payload)) {
            throw new HttpBadRequestException($request, 'Données d\'indisponibilité manquantes.');
        }

        $dto = new InputIndisponibiliteDTO(
            $praticienId,
            $payload['date_debut'],
            $payload['date_fin'],
            $payload['motif']
        );

        try {
            $indispo = $this->service->creerIndisponibilite($dto);
            $body = ['data' => $this->indisponibiliteResource($request, $indispo)];

            return $this->respondWithJson($response, $body, 201)
                ->withHeader('Location', '/praticiens/' . $praticienId . '/indisponibilites/' . $indispo->id);
        } catch (ResourceNotFoundException $exception) {
            throw new HttpNotFoundException($request, $exception->getMessage(), $exception);
        } catch (ValidationException $exception) {
            throw new HttpBadRequestException($request, $exception->getMessage(), $exception);
        } catch (Throwable $exception) {
            throw new HttpInternalServerErrorException($request, 'Une erreur interne est survenue.', $exception);
        }
    }
}
