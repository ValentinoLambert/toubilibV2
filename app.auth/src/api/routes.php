<?php
declare(strict_types=1);

use toubilib\api\actions\auth\LoginAction;
use toubilib\api\actions\auth\MeAction;
use toubilib\api\middlewares\AuthenticatedMiddleware;

return function( \Slim\App $app):\Slim\App {
    // Routes d'authentification
    $app->post('/auth/login', LoginAction::class)->setName('auth.login');
    $app->post('/auth/signin', LoginAction::class)->setName('auth.signin');
    $app->post('/auth/register', \toubilib\api\actions\patient\InscrirePatientAction::class)->setName('auth.register');
    $app->post('/auth/refresh', \toubilib\api\actions\auth\RefreshAction::class)->setName('auth.refresh');

    $app->get('/auth/me', MeAction::class)
        ->setName('auth.me')
        ->add(AuthenticatedMiddleware::class);

    // Route de validation de token (Exercice 3)
    $app->get('/tokens/validate', \toubilib\api\actions\auth\ValidateTokenAction::class)
        ->setName('tokens.validate');

    return $app;
};
