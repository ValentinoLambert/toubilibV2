<?php

namespace toubilib\core\application\usecases;

use toubilib\core\application\dto\InputPatientDTO;
use toubilib\core\application\dto\PatientDTO;
use toubilib\core\application\dto\RdvDTO;

interface ServicePatientInterface
{
    /**
     * Inscrit un nouveau patient (compte utilisateur + fiche patient).
     *
     * @throws \toubilib\core\application\exceptions\ValidationException
     */
    public function inscrirePatient(InputPatientDTO $dto): PatientDTO;

    /**
     * Retourne l'historique des rendez-vous d'un patient.
     *
     * @return RdvDTO[]
     * @throws \toubilib\core\application\exceptions\ResourceNotFoundException
     */
    public function listerHistoriquePatient(string $patientId): array;
}
