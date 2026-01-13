<?php
declare(strict_types=1);

namespace toubilib\api\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use toubilib\api\middlewares\AuthenticatedMiddleware;
use toubilib\core\application\dto\IndisponibiliteDTO;
use toubilib\core\application\dto\PraticienDTO;
use toubilib\core\application\dto\UserDTO;
use toubilib\core\domain\entities\user\UserRole;

abstract class AbstractAction
{
    /**
     * Encode un payload en JSON et force le Content-Type.
     */
    protected function respondWithJson(Response $response, mixed $payload, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }

    /**
     * Retourne une reponse JSON d'erreur avec message.
     */
    protected function respondWithError(Response $response, string $message, int $status): Response
    {
        $error = ['error' => ['message' => $message]];
        return $this->respondWithJson($response, $error, $status);
    }

    protected function praticienResource(Request $request, PraticienDTO $dto): array
    {
        $data = $dto->jsonSerialize();
        $id = $data['id'];
        unset($data['id']);

        /** @var UserDTO|null $user */
        $user = $request->getAttribute(AuthenticatedMiddleware::ATTRIBUTE_USER);

        $links = [
            'self' => ['href' => '/praticiens/' . $id, 'method' => 'GET'],
        ];

        if ($this->userHasAnyRole($user, ['admin', 'praticien'])) {
            $links['indisponibilites'] = [
                'href' => '/praticiens/' . $id . '/indisponibilites',
                'method' => 'GET',
            ];
        }

        return [
            'id' => $id,
            'type' => 'praticien',
            'attributes' => $data,
            '_links' => $links,
        ];
    }

    protected function indisponibiliteResource(Request $request, IndisponibiliteDTO $dto): array
    {
        $data = $dto->jsonSerialize();
        $id = $data['id'];
        unset($data['id']);

        return [
            'id' => $id,
            'type' => 'indisponibilite',
            'attributes' => $data,
            '_links' => [
                'self' => [
                    'href' => '/praticiens/' . $dto->praticien_id . '/indisponibilites/' . $id,
                    'method' => 'GET',
                ],
                'praticien' => ['href' => '/praticiens/' . $dto->praticien_id, 'method' => 'GET'],
                'supprimer' => [
                    'href' => '/praticiens/' . $dto->praticien_id . '/indisponibilites/' . $id,
                    'method' => 'DELETE',
                ],
            ],
        ];
    }

    protected function collectionLinks(string $href): array
    {
        return [
            'self' => ['href' => $href, 'method' => 'GET'],
        ];
    }

    protected function userHasAnyRole(?UserDTO $user, array $roles): bool
    {
        if ($user === null) {
            return false;
        }
        $roleName = strtolower(UserRole::toString($user->role));
        $allowed = array_map('strtolower', $roles);
        return in_array($roleName, $allowed, true);
    }
}
