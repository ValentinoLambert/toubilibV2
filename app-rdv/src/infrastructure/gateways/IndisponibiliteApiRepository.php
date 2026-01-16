<?php
declare(strict_types=1);

namespace toubilib\infra\gateways;

use DateTimeImmutable;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use toubilib\core\application\ports\IndisponibiliteRepositoryInterface;
use toubilib\core\domain\entities\praticien\Indisponibilite;

class IndisponibiliteApiRepository implements IndisponibiliteRepositoryInterface
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function findByPraticienBetween(string $praticienId, string $de, string $a): array
    {
        $deDate = $this->formatDate($de);
        $aDate = $this->formatDate($a);

        $response = $this->client->get(
            '/praticiens/' . $praticienId . '/indisponibilites',
            ['query' => ['de' => $deDate, 'a' => $aDate]]
        );

        if ($response->getStatusCode() !== 200) {
            return [];
        }

        $payload = $this->decodeJson($response);
        $items = $payload['data'] ?? [];
        if (!is_array($items)) {
            return [];
        }

        $indispos = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $attributes = $item['attributes'] ?? null;
            if (!is_array($attributes)) {
                continue;
            }

            $indispos[] = new Indisponibilite(
                (string)($item['id'] ?? ''),
                (string)($attributes['praticien_id'] ?? $praticienId),
                (string)($attributes['date_debut'] ?? ''),
                (string)($attributes['date_fin'] ?? ''),
                isset($attributes['motif']) ? (string)$attributes['motif'] : null
            );
        }

        return $indispos;
    }

    public function findOverlapping(string $praticienId, string $de, string $a): array
    {
        $indispos = $this->findByPraticienBetween($praticienId, $de, $a);
        $debut = new DateTimeImmutable($de);
        $fin = new DateTimeImmutable($a);

        return array_values(array_filter(
            $indispos,
            static fn(Indisponibilite $indispo) => $indispo->overlaps($debut, $fin)
        ));
    }

    public function findById(string $id): ?Indisponibilite
    {
        throw new \BadMethodCallException('Recherche par identifiant non supportée dans le service RDV.');
    }

    public function save(Indisponibilite $indisponibilite): Indisponibilite
    {
        throw new \BadMethodCallException('Création d\'indisponibilités non supportée dans le service RDV.');
    }

    public function delete(string $id): void
    {
        throw new \BadMethodCallException('Suppression d\'indisponibilités non supportée dans le service RDV.');
    }

    private function formatDate(string $value): string
    {
        return (new DateTimeImmutable($value))->format('Y-m-d');
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJson(ResponseInterface $response): array
    {
        $decoded = json_decode($response->getBody()->getContents(), true);
        if (!is_array($decoded)) {
            throw new RuntimeException('Réponse JSON invalide depuis le service praticiens.');
        }

        return $decoded;
    }
}
