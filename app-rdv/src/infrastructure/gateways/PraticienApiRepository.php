<?php
declare(strict_types=1);

namespace toubilib\infra\gateways;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use toubilib\core\application\ports\PraticienRepositoryInterface;
use toubilib\core\domain\entities\praticien\MotifVisite;
use toubilib\core\domain\entities\praticien\MoyenPaiement;
use toubilib\core\domain\entities\praticien\Praticien;
use toubilib\core\domain\entities\praticien\PraticienDetail;
use toubilib\core\domain\entities\praticien\Specialite;
use toubilib\core\domain\entities\praticien\Structure;

class PraticienApiRepository implements PraticienRepositoryInterface
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function findAll(): array
    {
        $response = $this->client->get('/praticiens');
        if ($response->getStatusCode() !== 200) {
            return [];
        }

        $payload = $this->decodeJson($response);
        $items = $payload['data'] ?? [];
        if (!is_array($items)) {
            return [];
        }

        $results = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $attributes = $item['attributes'] ?? null;
            if (!is_array($attributes)) {
                continue;
            }

            $id = (string)($item['id'] ?? '');
            if ($id === '') {
                continue;
            }

            $specialite = new Specialite(
                (int)($attributes['specialite_id'] ?? 0),
                (string)($attributes['specialite'] ?? '')
            );

            $results[] = new Praticien(
                $id,
                (string)($attributes['nom'] ?? ''),
                (string)($attributes['prenom'] ?? ''),
                (string)($attributes['ville'] ?? ''),
                (string)($attributes['email'] ?? ''),
                $specialite
            );
        }

        return $results;
    }

    public function findDetailById(string $id): ?PraticienDetail
    {
        $response = $this->client->get('/praticiens/' . $id);
        $status = $response->getStatusCode();
        if ($status === 404) {
            return null;
        }
        if ($status < 200 || $status >= 300) {
            throw new RuntimeException('Erreur lors de la récupération du praticien.');
        }

        $payload = $this->decodeJson($response);
        $data = $payload['data'] ?? null;
        if (!is_array($data)) {
            throw new RuntimeException('Réponse praticien invalide.');
        }

        return $this->mapDetail($data);
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

    /**
     * @param array<string, mixed> $data
     */
    private function mapDetail(array $data): PraticienDetail
    {
        $attributes = $data['attributes'] ?? [];
        if (!is_array($attributes)) {
            throw new RuntimeException('Attributs praticien manquants.');
        }

        $specialite = new Specialite(
            (int)($attributes['specialite_id'] ?? 0),
            (string)($attributes['specialite'] ?? '')
        );

        $structure = null;
        if (isset($attributes['structure']) && is_array($attributes['structure'])) {
            $structureData = $attributes['structure'];
            $structure = new Structure(
                (string)($structureData['id'] ?? ''),
                (string)($structureData['nom'] ?? ''),
                (string)($structureData['adresse'] ?? ''),
                isset($structureData['ville']) ? (string)$structureData['ville'] : null,
                isset($structureData['code_postal']) ? (string)$structureData['code_postal'] : null,
                isset($structureData['telephone']) ? (string)$structureData['telephone'] : null
            );
        }

        $motifs = $this->mapMotifs($attributes);
        $moyens = $this->mapMoyens($attributes);

        return new PraticienDetail(
            (string)($data['id'] ?? ''),
            (string)($attributes['nom'] ?? ''),
            (string)($attributes['prenom'] ?? ''),
            (string)($attributes['ville'] ?? ''),
            (string)($attributes['email'] ?? ''),
            (string)($attributes['telephone'] ?? ''),
            $specialite,
            $structure,
            $motifs,
            $moyens
        );
    }

    /**
     * @param array<string, mixed> $attributes
     * @return MotifVisite[]
     */
    private function mapMotifs(array $attributes): array
    {
        $motifs = [];
        $details = $attributes['motifs_details'] ?? null;
        if (is_array($details)) {
            foreach ($details as $motif) {
                if (!is_array($motif)) {
                    continue;
                }
                $motifs[] = new MotifVisite(
                    (int)($motif['id'] ?? 0),
                    (string)($motif['libelle'] ?? '')
                );
            }
            return $motifs;
        }

        $labels = $attributes['motifs'] ?? null;
        if (is_array($labels)) {
            foreach ($labels as $label) {
                $motifs[] = new MotifVisite(0, (string)$label);
            }
        }

        return $motifs;
    }

    /**
     * @param array<string, mixed> $attributes
     * @return MoyenPaiement[]
     */
    private function mapMoyens(array $attributes): array
    {
        $moyens = [];
        $details = $attributes['moyens_details'] ?? null;
        if (is_array($details)) {
            foreach ($details as $moyen) {
                if (!is_array($moyen)) {
                    continue;
                }
                $moyens[] = new MoyenPaiement(
                    (int)($moyen['id'] ?? 0),
                    (string)($moyen['libelle'] ?? '')
                );
            }
            return $moyens;
        }

        $labels = $attributes['moyens'] ?? null;
        if (is_array($labels)) {
            foreach ($labels as $label) {
                $moyens[] = new MoyenPaiement(0, (string)$label);
            }
        }

        return $moyens;
    }
}
