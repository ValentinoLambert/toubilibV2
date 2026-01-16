<?php
declare(strict_types=1);

namespace toubilib\api\middlewares;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validator;
use toubilib\core\application\dto\InputRendezVousDTO;
use toubilib\core\application\exceptions\ValidationException;

class CreateRendezVousMiddleware implements MiddlewareInterface
{
    public const ATTRIBUTE_DTO = 'rdv.input_dto';

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
     * @return InputRendezVousDTO
     */
    private function buildDto(array $data): InputRendezVousDTO
    {
        $schema = Validator::arrayType()
            ->key('praticien_id', Validator::uuid())
            ->key('patient_id', Validator::uuid())
            ->key('motif_id', Validator::digit()->positive())
            ->key('date_heure_debut', Validator::dateTime('Y-m-d H:i:s'))
            ->key('duree', Validator::intType()->positive());

        try {
            $schema->assert($data);
        } catch (NestedValidationException $exception) {
            throw new ValidationException($exception->getFullMessage(), previous: $exception);
        }

        return new InputRendezVousDTO(
            trim((string)$data['praticien_id']),
            trim((string)$data['patient_id']),
            (string)$data['date_heure_debut'],
            trim((string)$data['motif_id']),
            (int)$data['duree']
        );
    }
}
