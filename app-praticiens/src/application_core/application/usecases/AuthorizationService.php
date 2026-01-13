<?php

namespace toubilib\core\application\usecases;

use toubilib\core\application\dto\UserDTO;
use toubilib\core\application\exceptions\AuthorizationException;
use toubilib\core\domain\entities\user\UserRole;

class AuthorizationService implements AuthorizationServiceInterface
{
    public function assertCanManageIndisponibilite(UserDTO $user, string $praticienId): void
    {
        $role = UserRole::toString($user->role);

        if ($role === 'admin') {
            return;
        }

        if ($role === 'praticien' && $user->id === $praticienId) {
            return;
        }

        throw new AuthorizationException('Gestion des indisponibilites refusee pour ce praticien.');
    }
}
