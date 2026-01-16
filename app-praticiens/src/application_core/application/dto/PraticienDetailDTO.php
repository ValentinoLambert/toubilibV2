<?php

namespace toubilib\core\application\dto;

class PraticienDetailDTO implements \JsonSerializable
{
    public string $id;
    public string $nom;
    public string $prenom;
    public string $ville;
    public string $email;
    public string $telephone;
    public string $specialite;
    public ?array $structure; // ['nom','adresse','ville','code_postal','telephone']
    /** @var string[] */
    public array $motifs;
    /** @var array[] */
    public array $motifs_details;
    /** @var string[] */
    public array $moyens;
    /** @var array[] */
    public array $moyens_details;

    public function __construct(
        string $id,
        string $nom,
        string $prenom,
        string $ville,
        string $email,
        string $telephone,
        string $specialite,
        ?array $structure,
        array $motifs,
        array $moyens,
        array $motifs_details = [],
        array $moyens_details = []
    ) {
        $this->id = $id;
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->ville = $ville;
        $this->email = $email;
        $this->telephone = $telephone;
        $this->specialite = $specialite;
        $this->structure = $structure;
        $this->motifs = $motifs;
        $this->motifs_details = $motifs_details;
        $this->moyens = $moyens;
        $this->moyens_details = $moyens_details;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'ville' => $this->ville,
            'email' => $this->email,
            'telephone' => $this->telephone,
            'specialite' => $this->specialite,
            'structure' => $this->structure,
            'motifs' => $this->motifs,
            'motifs_details' => $this->motifs_details,
            'moyens' => $this->moyens,
            'moyens_details' => $this->moyens_details,
        ];
    }
}
