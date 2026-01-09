<?php
declare(strict_types=1);

namespace toubilib\api\middlewares;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Ramsey\Uuid\Uuid;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validator;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpInternalServerErrorException;
use Slim\Routing\RouteContext;
use toubilib\core\application\dto\InputIndisponibiliteDTO;
use toubilib\core\application\exceptions\ValidationException;

class CreateIndisponibiliteMiddleware implements MiddlewareInterface
{
    public const ATTRIBUTE_DTO = 'indispo.input_dto';

    public function process(Request $request, Handler $handler): Response
    {
        $data = $request->getParsedBody();
        if (!is_array($data)) {
            throw new ValidationException('Corps de requête JSON invalide.');
        }

        $route = RouteContext::fromRequest($request)->getRoute();
        if ($route === null) {
            throw new HttpInternalServerErrorException($request, 'Route introuvable pour la création d\'indisponibilité.');
        }

        $praticienId = (string)$route->getArgument('id');
        if (!Uuid::isValid($praticienId)) {
            throw new HttpBadRequestException($request, 'Identifiant praticien invalide.');
        }

        $dto = $this->buildDto($praticienId, $data);
        $request = $request->withAttribute(self::ATTRIBUTE_DTO, $dto);

        return $handler->handle($request);
    }

    /**
     * @param array<string, mixed> $data
     * @return InputIndisponibiliteDTO
     */
    private function buildDto(string $praticienId, array $data): InputIndisponibiliteDTO
    {
        $schema = Validator::arrayType()
            ->key('date_debut', Validator::dateTime('Y-m-d H:i:s'))
            ->key('date_fin', Validator::dateTime('Y-m-d H:i:s'))
            ->key('motif', Validator::optional(Validator::stringType()->length(0, 255)));

        try {
            $schema->assert($data);
        } catch (NestedValidationException $exception) {
            throw new ValidationException($exception->getFullMessage(), previous: $exception);
        }

        return new InputIndisponibiliteDTO(
            $praticienId,
            (string)$data['date_debut'],
            (string)$data['date_fin'],
            isset($data['motif']) ? trim((string)$data['motif']) : null
        );
    }
}
