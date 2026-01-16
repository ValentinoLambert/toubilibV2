<?php
declare(strict_types=1);

namespace toubilib\api\actions\patient;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpInternalServerErrorException;
use Throwable;
use toubilib\api\actions\AbstractAction;
use toubilib\api\middlewares\InscriptionPatientMiddleware;
use toubilib\api\provider\AuthProviderInterface;
use toubilib\core\application\dto\InputPatientDTO;
use toubilib\core\application\exceptions\ResourceNotFoundException;
use toubilib\core\application\exceptions\ValidationException;
use toubilib\core\application\usecases\ServicePatientInterface;
use toubilib\core\domain\exceptions\InvalidCredentialsException;

class InscrirePatientAction extends AbstractAction
{
    private ServicePatientInterface $service;
    private AuthProviderInterface $authProvider;

    public function __construct(ServicePatientInterface $service, AuthProviderInterface $authProvider)
    {
        $this->service = $service;
        $this->authProvider = $authProvider;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $dto = $request->getAttribute(InscriptionPatientMiddleware::ATTRIBUTE_DTO);
        if (!$dto instanceof InputPatientDTO) {
            throw new HttpBadRequestException($request, 'Données d\'inscription manquantes.');
        }

        try {
            $patient = $this->service->inscrirePatient($dto);
            $auth = $this->authProvider->signin($dto->email, $dto->password);

            $body = [
                'data' => $this->patientResource($request, $patient),
                'tokens' => [
                    'access_token' => $auth->accessToken,
                    'refresh_token' => $auth->refreshToken,
                    'token_type' => 'Bearer',
                    'expires_in' => $auth->expiresIn,
                    'refresh_expires_in' => $auth->refreshExpiresIn,
                ],
            ];

            return $this->respondWithJson($response, $body, 201)
                ->withHeader('Location', '/patients/' . $patient->id);
        } catch (ResourceNotFoundException|ValidationException $exception) {
            throw new HttpBadRequestException($request, $exception->getMessage(), $exception);
        } catch (InvalidCredentialsException $exception) {
            throw new HttpInternalServerErrorException($request, 'Authentification impossible juste après la création.', $exception);
        } catch (Throwable $exception) {
            throw new HttpInternalServerErrorException($request, 'Une erreur interne est survenue.', $exception);
        }
    }
}
