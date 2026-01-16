<?php
declare(strict_types=1);

namespace toubilib\api\middlewares;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validator;
use toubilib\core\application\dto\InputPatientDTO;
use toubilib\core\application\exceptions\ValidationException;

class InscriptionPatientMiddleware implements MiddlewareInterface
{
    public const ATTRIBUTE_DTO = 'patient.input_dto';

    public function process(Request $request, Handler $handler): Response
    {
        $data = $request->getParsedBody();
        if (!is_array($data)) {
            throw new ValidationException('Corps de requête JSON invalide.');
        }

        $dto = $this->buildDto($data);
        $request = $request->withAttribute(self::ATTRIBUTE_DTO, $dto);

        return $handler->handle($request);
    }

    /**
     * @param array<string, mixed> $data
     * @return InputPatientDTO
     */
    private function buildDto(array $data): InputPatientDTO
    {
        $schema = Validator::arrayType()
            ->key('nom', Validator::stringType()->length(2, null))
            ->key('prenom', Validator::stringType()->length(2, null))
            ->key('email', Validator::stringType()->email())
            ->key('password', Validator::stringType()->length(6, null))
            ->key('telephone', Validator::stringType()->length(6, null))
            ->key('date_naissance', Validator::optional(Validator::date('Y-m-d')))
            ->key('adresse', Validator::optional(Validator::stringType()->notEmpty()))
            ->key('code_postal', Validator::optional(Validator::stringType()->length(3, 12)))
            ->key('ville', Validator::optional(Validator::stringType()->length(2, null)));

        try {
            $schema->assert($data);
        } catch (NestedValidationException $exception) {
            throw new ValidationException($exception->getFullMessage(), previous: $exception);
        }

        return new InputPatientDTO(
            trim((string)$data['nom']),
            trim((string)$data['prenom']),
            strtolower(trim((string)$data['email'])),
            (string)$data['password'],
            trim((string)$data['telephone']),
            isset($data['date_naissance']) ? (string)$data['date_naissance'] : null,
            isset($data['adresse']) ? trim((string)$data['adresse']) : null,
            isset($data['code_postal']) ? trim((string)$data['code_postal']) : null,
            isset($data['ville']) ? trim((string)$data['ville']) : null
        );
    }
}
