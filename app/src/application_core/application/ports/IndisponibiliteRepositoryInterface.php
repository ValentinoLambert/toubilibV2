<?php

namespace toubilib\core\application\ports;

use toubilib\core\domain\entities\praticien\Indisponibilite;

interface IndisponibiliteRepositoryInterface
{
    /**
     * @return Indisponibilite[]
     */
    public function findByPraticienBetween(string $praticienId, string $de, string $a): array;

    /**
     * @return Indisponibilite[]
     */
    public function findOverlapping(string $praticienId, string $de, string $a): array;

    public function findById(string $id): ?Indisponibilite;

    public function save(Indisponibilite $indisponibilite): Indisponibilite;

    public function delete(string $id): void;
}
