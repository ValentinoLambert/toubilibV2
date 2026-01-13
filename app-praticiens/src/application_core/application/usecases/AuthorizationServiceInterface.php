<?php

namespace toubilib\core\application\usecases;

use toubilib\core\application\dto\UserDTO;
use toubilib\core\application\exceptions\AuthorizationException;

interface AuthorizationServiceInterface
{
    /**
     * Verifie la capacite a gerer les indisponibilites d'un praticien.
     *
     * @throws AuthorizationException
     */
    public function assertCanManageIndisponibilite(UserDTO $user, string $praticienId): void;
}
