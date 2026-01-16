<?php

namespace toubilib\core\application\usecases;

use Ramsey\Uuid\Uuid;
use Throwable;
use toubilib\core\application\dto\InputPatientDTO;
use toubilib\core\application\dto\PatientDTO;
use toubilib\core\application\dto\RdvDTO;
use toubilib\core\application\exceptions\ResourceNotFoundException;
use toubilib\core\application\exceptions\ValidationException;
use toubilib\core\application\ports\PatientRepositoryInterface;
use toubilib\core\application\ports\RdvRepositoryInterface;
use toubilib\core\application\ports\UserRepositoryInterface;
use toubilib\core\domain\entities\patient\Patient;
use toubilib\core\domain\entities\rdv\Rdv;
use toubilib\core\domain\entities\user\User;
use toubilib\core\domain\entities\user\UserRole;
use toubilib\core\domain\exceptions\DuplicateUserException;
use toubilib\core\domain\exceptions\UserNotFoundException;

class ServicePatient implements ServicePatientInterface
{
    private PatientRepositoryInterface $patientRepository;
    private UserRepositoryInterface $userRepository;
    private RdvRepositoryInterface $rdvRepository;

    public function __construct(
        PatientRepositoryInterface $patientRepository,
        UserRepositoryInterface $userRepository,
        RdvRepositoryInterface $rdvRepository
    ) {
        $this->patientRepository = $patientRepository;
        $this->userRepository = $userRepository;
        $this->rdvRepository = $rdvRepository;
    }

    public function inscrirePatient(InputPatientDTO $dto): PatientDTO
    {
        $this->validateInscription($dto);

        if ($this->patientRepository->findByEmail($dto->email) !== null) {
            throw new ValidationException('Un patient existe déjà avec cet email.');
        }

        try {
            $this->userRepository->findByEmail($dto->email);
            throw new ValidationException('Un utilisateur existe déjà avec cet email.');
        } catch (UserNotFoundException) {
            // OK
        }

        $id = Uuid::uuid4()->toString();
        $user = new User(
            $id,
            $dto->email,
            password_hash($dto->password, PASSWORD_DEFAULT),
            UserRole::PATIENT
        );

        $patient = new Patient(
            $id,
            $dto->nom,
            $dto->prenom,
            $dto->email,
            $dto->telephone,
            $dto->date_naissance,
            $dto->adresse,
            $dto->code_postal,
            $dto->ville
        );

        try {
            $this->userRepository->save($user);
            $this->patientRepository->save($patient);
        } catch (DuplicateUserException $exception) {
            throw new ValidationException('Un utilisateur existe déjà avec cet email.', previous: $exception);
        } catch (Throwable $exception) {
            try {
                $this->userRepository->delete($id);
            } catch (Throwable) {
                // rollback best effort
            }
            throw $exception instanceof ValidationException
                ? $exception
                : new ValidationException('Impossible de créer le patient.', previous: $exception);
        }

        return PatientDTO::fromEntity($patient);
    }

    public function listerHistoriquePatient(string $patientId): array
    {
        if (!Uuid::isValid($patientId)) {
            throw new ValidationException('Identifiant patient invalide.');
        }

        $patient = $this->patientRepository->findById($patientId);
        if ($patient === null) {
            throw new ResourceNotFoundException(sprintf('Patient %s introuvable', $patientId));
        }

        $rdvs = $this->rdvRepository->findByPatient($patientId);
        return array_map(fn(Rdv $rdv) => $this->mapToDto($rdv), $rdvs);
    }

    private function validateInscription(InputPatientDTO $dto): void
    {
        if ($dto->nom === '' || $dto->prenom === '' || $dto->email === '' || $dto->password === '' || $dto->telephone === '') {
            throw new ValidationException('Tous les champs obligatoires doivent être renseignés.');
        }

        if (!filter_var($dto->email, FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException('Email invalide.');
        }

        if (strlen($dto->password) < 6) {
            throw new ValidationException('Le mot de passe doit contenir au moins 6 caractères.');
        }
    }

    private function mapToDto(Rdv $rdv): RdvDTO
    {
        return new RdvDTO(
            $rdv->id,
            $rdv->praticien_id,
            $rdv->patient_id,
            $rdv->patient_email,
            $rdv->date_heure_debut,
            $rdv->status,
            $rdv->duree,
            $rdv->getDateHeureFin()->format('Y-m-d H:i:s'),
            $rdv->date_creation,
            $rdv->motif_visite
        );
    }
}
