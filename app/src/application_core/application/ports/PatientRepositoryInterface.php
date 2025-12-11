<?php

namespace toubilib\core\application\ports;

use toubilib\core\domain\entities\patient\Patient;

interface PatientRepositoryInterface
{
    public function findById(string $id): ?Patient;

    public function findByEmail(string $email): ?Patient;

    public function save(Patient $patient): Patient;
}
