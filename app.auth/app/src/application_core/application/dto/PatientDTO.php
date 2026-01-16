<?php

namespace toubilib\core\application\dto;

use toubilib\core\domain\entities\patient\Patient;

class PatientDTO implements \JsonSerializable
{
    public string $id;
    public string $nom;
    public string $prenom;
    public ?string $email;
    public ?string $telephone;
    public ?string $date_naissance;
    public ?string $adresse;
    public ?string $code_postal;
    public ?string $ville;

    public function __construct(
        string $id,
        string $nom,
        string $prenom,
        ?string $email,
        ?string $telephone,
        ?string $date_naissance,
        ?string $adresse,
        ?string $code_postal,
        ?string $ville
    ) {
        $this->id = $id;
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->email = $email;
        $this->telephone = $telephone;
        $this->date_naissance = $date_naissance;
        $this->adresse = $adresse;
        $this->code_postal = $code_postal;
        $this->ville = $ville;
    }

    public static function fromEntity(Patient $patient): self
    {
        return new self(
            $patient->id,
            $patient->nom,
            $patient->prenom,
            $patient->email,
            $patient->telephone,
            $patient->date_naissance,
            $patient->adresse,
            $patient->code_postal,
            $patient->ville
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'email' => $this->email,
            'telephone' => $this->telephone,
            'date_naissance' => $this->date_naissance,
            'adresse' => $this->adresse,
            'code_postal' => $this->code_postal,
            'ville' => $this->ville,
        ];
    }
}
