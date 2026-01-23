<?php

namespace toubilib\core\application\usecases;

use toubilib\core\application\dto\RdvDTO;
use toubilib\core\application\dto\UserDTO;
use toubilib\core\application\exceptions\AuthorizationException;
use toubilib\core\domain\entities\user\UserRole;

class AuthorizationService implements AuthorizationServiceInterface
{
    private ServiceRDVInterface $rdvService;

    public function __construct(ServiceRDVInterface $rdvService)
    {
        $this->rdvService = $rdvService;
    }

    public function assertCanAccessAgenda(UserDTO $user, string $praticienId): void
    {
        $role = UserRole::toString($user->role);

        if ($role === 'praticien' && $user->id === $praticienId) {
            return;
        }

        throw new AuthorizationException("AccÇùs Çÿ l'agenda refusÇ¸.");
    }

    public function assertCanViewRdv(UserDTO $user, string $rdvId): RdvDTO
    {
        $rdv = $this->rdvService->consulterRdv($rdvId);
        $role = UserRole::toString($user->role);

        if ($role === 'praticien' && $rdv->praticien_id === $user->id) {
            return $rdv;
        }

        if ($role === 'patient' && $rdv->patient_id === $user->id) {
            return $rdv;
        }

        throw new AuthorizationException("AccÇùs au rendez-vous refusÇ¸.");
    }

    public function assertCanCreateRdv(UserDTO $user, string $patientId, string $praticienId): void
    {
        $role = UserRole::toString($user->role);

        if ($role === 'patient' && $user->id === $patientId) {
            return;
        }

        if ($role === 'praticien' && $user->id === $praticienId) {
            return;
        }

        throw new AuthorizationException('CrÇ¸ation de rendez-vous refusÇ¸e pour cet utilisateur.');
    }

    public function assertCanCancelRdv(UserDTO $user, string $rdvId): RdvDTO
    {
        $rdv = $this->rdvService->consulterRdv($rdvId);
        $role = UserRole::toString($user->role);

        if ($role === 'admin') {
            return $rdv;
        }

        if ($role === 'praticien' && $rdv->praticien_id === $user->id) {
            return $rdv;
        }

        if ($role === 'patient' && $rdv->patient_id === $user->id) {
            return $rdv;
        }

        throw new AuthorizationException("Annulation du rendez-vous refusÇ¸e.");
    }

    public function assertCanUpdateRdvStatus(UserDTO $user, string $rdvId): RdvDTO
    {
        $rdv = $this->rdvService->consulterRdv($rdvId);
        $role = UserRole::toString($user->role);

        if ($role === 'admin') {
            return $rdv;
        }

        if ($role === 'praticien' && $rdv->praticien_id === $user->id) {
            return $rdv;
        }

        throw new AuthorizationException('Modification du statut refusÇ¸e.');
    }
}
