<?php
declare(strict_types=1);

use toubilib\api\actions\auth\LoginAction;
use toubilib\api\actions\auth\MeAction;
use toubilib\api\actions\praticien\ListerPraticiensAction;
use toubilib\api\actions\praticien\AfficherPraticienAction;
use toubilib\api\actions\praticien\ListerIndisponibilitesAction;
use toubilib\api\actions\praticien\CreerIndisponibiliteAction;
use toubilib\api\actions\praticien\SupprimerIndisponibiliteAction;
use toubilib\api\middlewares\AuthenticatedMiddleware;
use toubilib\api\middlewares\CanManageIndisponibiliteMiddleware;
use toubilib\api\middlewares\CreateIndisponibiliteMiddleware;
use toubilib\api\middlewares\OptionalAuthMiddleware;
use toubilib\api\middlewares\RequireRoleMiddleware;

return function( \Slim\App $app):\Slim\App {
    $app->post('/auth/login', LoginAction::class)
        ->setName('auth.login');
    $app->get('/auth/me', MeAction::class)
        ->setName('auth.me')
        ->add(AuthenticatedMiddleware::class);

    $app->get('/praticiens', ListerPraticiensAction::class)
        ->setName('praticiens.list')
        ->add(OptionalAuthMiddleware::class);

    $app->get('/praticiens/{id}', AfficherPraticienAction::class)
        ->setName('praticiens.detail')
        ->add(OptionalAuthMiddleware::class);

    $app->get('/praticiens/{id}/indisponibilites', ListerIndisponibilitesAction::class)
        ->setName('praticiens.indisponibilites.list')
        ->add(CanManageIndisponibiliteMiddleware::class)
        ->add(new RequireRoleMiddleware(['admin', 'praticien']))
        ->add(AuthenticatedMiddleware::class);

    $app->post('/praticiens/{id}/indisponibilites', CreerIndisponibiliteAction::class)
        ->setName('praticiens.indisponibilites.create')
        ->add(CanManageIndisponibiliteMiddleware::class)
        ->add(new RequireRoleMiddleware(['admin', 'praticien']))
        ->add(AuthenticatedMiddleware::class)
        ->add(CreateIndisponibiliteMiddleware::class);

    $app->delete('/praticiens/{id}/indisponibilites/{indispoId}', SupprimerIndisponibiliteAction::class)
        ->setName('praticiens.indisponibilites.delete')
        ->add(CanManageIndisponibiliteMiddleware::class)
        ->add(new RequireRoleMiddleware(['admin', 'praticien']))
        ->add(AuthenticatedMiddleware::class);

    return $app;
};
