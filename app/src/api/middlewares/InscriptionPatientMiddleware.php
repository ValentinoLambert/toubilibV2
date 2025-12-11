<?php
declare(strict_types=1);

namespace toubilib\api\middlewares;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validator;
use toubilib\core\application\exceptions\ValidationException;

class InscriptionPatientMiddleware implements MiddlewareInterface
{
    public const ATTRIBUTE_PAYLOAD = 'patient.payload';

    public function process(Request $request, Handler $handler): Response
    {
        $data = $request->getParsedBody();
        if (!is_array($data)) {
            throw new ValidationException('Corps de requête JSON invalide.');
        }

        $payload = $this->validate($data);
        $request = $request->withAttribute(self::ATTRIBUTE_PAYLOAD, $payload);

        return $handler->handle($request);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function validate(array $data): array
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

        return [
            'nom' => trim((string)$data['nom']),
            'prenom' => trim((string)$data['prenom']),
            'email' => strtolower(trim((string)$data['email'])),
            'password' => (string)$data['password'],
            'telephone' => trim((string)$data['telephone']),
            'date_naissance' => isset($data['date_naissance']) ? (string)$data['date_naissance'] : null,
            'adresse' => isset($data['adresse']) ? trim((string)$data['adresse']) : null,
            'code_postal' => isset($data['code_postal']) ? trim((string)$data['code_postal']) : null,
            'ville' => isset($data['ville']) ? trim((string)$data['ville']) : null,
        ];
    }
}
