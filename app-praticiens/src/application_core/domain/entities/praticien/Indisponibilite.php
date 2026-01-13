<?php

namespace toubilib\core\domain\entities\praticien;

use DateTimeImmutable;

class Indisponibilite
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
        ?string $motif = null
    ) {
        $this->id = $id;
        $this->praticien_id = $praticien_id;
        $this->date_debut = $date_debut;
        $this->date_fin = $date_fin;
        $this->motif = $motif;
    }

    public function getDateDebut(): DateTimeImmutable
    {
        return new DateTimeImmutable($this->date_debut);
    }

    public function getDateFin(): DateTimeImmutable
    {
        return new DateTimeImmutable($this->date_fin);
    }

    public function overlaps(DateTimeImmutable $debut, DateTimeImmutable $fin): bool
    {
        return $this->getDateDebut() < $fin && $debut < $this->getDateFin();
    }
}
