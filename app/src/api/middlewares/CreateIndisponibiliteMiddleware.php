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

class CreateIndisponibiliteMiddleware implements MiddlewareInterface
{
    public const ATTRIBUTE_PAYLOAD = 'indispo.payload';

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
     * @return array{date_debut: string, date_fin: string, motif: ?string}
     */
    private function validate(array $data): array
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

        return [
            'date_debut' => (string)$data['date_debut'],
            'date_fin' => (string)$data['date_fin'],
            'motif' => isset($data['motif']) ? trim((string)$data['motif']) : null,
        ];
    }
}
