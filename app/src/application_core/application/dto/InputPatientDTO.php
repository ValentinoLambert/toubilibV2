<?php

namespace toubilib\core\application\dto;

class InputPatientDTO
{
    public string $nom;
    public string $prenom;
    public string $email;
    public string $password;
    public string $telephone;
    public ?string $date_naissance;
    public ?string $adresse;
    public ?string $code_postal;
    public ?string $ville;

    public function __construct(
        string $nom,
        string $prenom,
        string $email,
        string $password,
        string $telephone,
        ?string $date_naissance = null,
        ?string $adresse = null,
        ?string $code_postal = null,
        ?string $ville = null
    ) {
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->email = $email;
        $this->password = $password;
        $this->telephone = $telephone;
        $this->date_naissance = $date_naissance;
        $this->adresse = $adresse;
        $this->code_postal = $code_postal;
        $this->ville = $ville;
    }
}
