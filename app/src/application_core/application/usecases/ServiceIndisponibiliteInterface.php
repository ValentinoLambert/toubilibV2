<?php

namespace toubilib\core\application\usecases;

use toubilib\core\application\dto\IndisponibiliteDTO;
use toubilib\core\application\dto\InputIndisponibiliteDTO;

interface ServiceIndisponibiliteInterface
{
    /**
     * @return IndisponibiliteDTO[]
     */
    public function listerIndisponibilites(string $praticienId, string $dateDebut, string $dateFin): array;

    public function creerIndisponibilite(InputIndisponibiliteDTO $dto): IndisponibiliteDTO;

    /**
     * @param string $id Identifiant de l'indisponibilité
     * @param string|null $praticienId Optionnel, permet de vérifier l'appartenance
     */
    public function supprimerIndisponibilite(string $id, ?string $praticienId = null): void;
}
