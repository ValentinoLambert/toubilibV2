<?php

namespace toubilib\infra\repositories;

use PDO;
use toubilib\core\application\ports\PatientRepositoryInterface;
use toubilib\core\domain\entities\patient\Patient;

class PDOPatientRepository implements PatientRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findById(string $id): ?Patient
    {
        $sql = 'SELECT * FROM patient WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;

        return $this->mapRowToPatient($row);
    }

    public function findByEmail(string $email): ?Patient
    {
        $sql = 'SELECT * FROM patient WHERE email = :email LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;

        return $this->mapRowToPatient($row);
    }

    public function save(Patient $patient): Patient
    {
        $exists = $this->findById($patient->id);
        if ($exists === null) {
            $sql = 'INSERT INTO patient (id, nom, prenom, date_naissance, adresse, code_postal, ville, email, telephone)'
                 . ' VALUES (:id, :nom, :prenom, :date_naissance, :adresse, :code_postal, :ville, :email, :telephone)';
        } else {
            $sql = 'UPDATE patient SET nom = :nom, prenom = :prenom, date_naissance = :date_naissance, adresse = :adresse,'
                 . ' code_postal = :code_postal, ville = :ville, email = :email, telephone = :telephone WHERE id = :id';
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id' => $patient->id,
            ':nom' => $patient->nom,
            ':prenom' => $patient->prenom,
            ':date_naissance' => $patient->date_naissance,
            ':adresse' => $patient->adresse,
            ':code_postal' => $patient->code_postal,
            ':ville' => $patient->ville,
            ':email' => $patient->email,
            ':telephone' => $patient->telephone,
        ]);

        return $patient;
    }

    private function mapRowToPatient(array $row): Patient
    {
        return new Patient(
            (string)$row['id'],
            (string)$row['nom'],
            (string)$row['prenom'],
            $row['email'] !== null ? (string)$row['email'] : null,
            $row['telephone'] !== null ? (string)$row['telephone'] : null,
            $row['date_naissance'] !== null ? (string)$row['date_naissance'] : null,
            $row['adresse'] !== null ? (string)$row['adresse'] : null,
            $row['code_postal'] !== null ? (string)$row['code_postal'] : null,
            $row['ville'] !== null ? (string)$row['ville'] : null
        );
    }
}
