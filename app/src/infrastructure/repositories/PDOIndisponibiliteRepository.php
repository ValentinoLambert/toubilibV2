<?php

namespace toubilib\infra\repositories;

use toubilib\core\application\ports\IndisponibiliteRepositoryInterface;
use toubilib\core\domain\entities\praticien\Indisponibilite;

class PDOIndisponibiliteRepository implements IndisponibiliteRepositoryInterface
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findByPraticienBetween(string $praticienId, string $de, string $a): array
    {
        $sql = $this->baseSelect()
            . ' WHERE praticien_id = :pid'
            . '   AND date_debut < :fin'
            . '   AND date_fin > :debut'
            . ' ORDER BY date_debut ASC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':pid' => $praticienId, ':fin' => $a, ':debut' => $de]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return array_map(fn($row) => $this->mapRowToEntity($row), $rows);
    }

    public function findOverlapping(string $praticienId, string $de, string $a): array
    {
        return $this->findByPraticienBetween($praticienId, $de, $a);
    }

    public function findById(string $id): ?Indisponibilite
    {
        $sql = $this->baseSelect() . ' WHERE id = :id LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ? $this->mapRowToEntity($row) : null;
    }

    public function save(Indisponibilite $indisponibilite): Indisponibilite
    {
        $existing = $this->findById($indisponibilite->id);
        if ($existing === null) {
            $sql = 'INSERT INTO indisponibilite (id, praticien_id, date_debut, date_fin, motif)'
                . ' VALUES (:id, :pid, :debut, :fin, :motif)';
        } else {
            $sql = 'UPDATE indisponibilite SET praticien_id = :pid, date_debut = :debut, date_fin = :fin, motif = :motif'
                . ' WHERE id = :id';
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id' => $indisponibilite->id,
            ':pid' => $indisponibilite->praticien_id,
            ':debut' => $indisponibilite->date_debut,
            ':fin' => $indisponibilite->date_fin,
            ':motif' => $indisponibilite->motif,
        ]);

        return $indisponibilite;
    }

    public function delete(string $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM indisponibilite WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    private function baseSelect(): string
    {
        return 'SELECT * FROM indisponibilite';
    }

    private function mapRowToEntity(array $row): Indisponibilite
    {
        return new Indisponibilite(
            (string)$row['id'],
            (string)$row['praticien_id'],
            (string)$row['date_debut'],
            (string)$row['date_fin'],
            $row['motif'] !== null ? (string)$row['motif'] : null
        );
    }
}
