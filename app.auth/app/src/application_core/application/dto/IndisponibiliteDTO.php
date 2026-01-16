<?php

namespace toubilib\core\application\dto;

class IndisponibiliteDTO implements \JsonSerializable
{
    public string $id;
    public string $praticien_id;
    public string $date_debut;
    public string $date_fin;
    public ?string $motif;

    public function __construct(
        string $id,
        string $praticien_id,
        string $date_debut,
        string $date_fin,
        ?string $motif
    ) {
        $this->id = $id;
        $this->praticien_id = $praticien_id;
        $this->date_debut = $date_debut;
        $this->date_fin = $date_fin;
        $this->motif = $motif;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'praticien_id' => $this->praticien_id,
            'date_debut' => $this->date_debut,
            'date_fin' => $this->date_fin,
            'motif' => $this->motif,
        ];
    }
}
