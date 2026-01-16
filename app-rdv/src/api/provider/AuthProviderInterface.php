<?php
declare(strict_types=1);

namespace toubilib\api\provider;

use toubilib\api\dto\AuthTokensDTO;
use toubilib\core\application\dto\UserDTO;

interface AuthProviderInterface
{
    /**
     * Authentifie l'utilisateur et génère les jetons associés.
     */
    public function signin(string $email, string $password): AuthTokensDTO;

    /**
     * Résout l'utilisateur associé à un jeton d'accès.
     */
    public function authenticateAccessToken(string $token): UserDTO;
}
