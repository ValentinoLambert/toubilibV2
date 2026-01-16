<?php
declare(strict_types=1);

use toubilib\api\actions\auth\LoginAction;
use toubilib\api\actions\auth\MeAction;
use toubilib\api\actions\praticien\ListerCreneauxOccupesAction;
use toubilib\api\actions\praticien\ListerAgendaAction;
use toubilib\api\actions\rdv\ConsulterRdvAction;
use toubilib\api\actions\rdv\CreerRdvAction;
use toubilib\api\actions\rdv\AnnulerRdvAction;
use toubilib\api\actions\rdv\ModifierStatutRdvAction;
use toubilib\api\middlewares\AuthenticatedMiddleware;
use toubilib\api\middlewares\CanAccessAgendaMiddleware;
use toubilib\api\middlewares\CanCancelRdvMiddleware;
use toubilib\api\middlewares\CanCreateRdvMiddleware;
use toubilib\api\middlewares\CanUpdateRdvStatusMiddleware;
use toubilib\api\middlewares\CanViewRdvMiddleware;
use toubilib\api\middlewares\CreateRendezVousMiddleware;
use toubilib\api\middlewares\OptionalAuthMiddleware;
use toubilib\api\middlewares\RequireRoleMiddleware;

return function( \Slim\App $app):\Slim\App {
    $app->post('/auth/login', LoginAction::class)
        ->setName('auth.login');
    $app->get('/auth/me', MeAction::class)
        ->setName('auth.me')
        ->add(AuthenticatedMiddleware::class);

    $app->get('/praticiens/{id}/rdv/occupes', ListerCreneauxOccupesAction::class)
        ->setName('praticiens.rdv_occupes')
        ->add(OptionalAuthMiddleware::class);

    $app->get('/praticiens/{id}/agenda', ListerAgendaAction::class)
        ->setName('praticiens.agenda')
        ->add(CanAccessAgendaMiddleware::class)
        ->add(AuthenticatedMiddleware::class);

    $app->get('/rdv/{id}', ConsulterRdvAction::class)
        ->setName('rdv.detail')
        ->add(CanViewRdvMiddleware::class)
        ->add(AuthenticatedMiddleware::class);

    $app->post('/rdv', CreerRdvAction::class)
        ->setName('rdv.create')
        ->add(CanCreateRdvMiddleware::class)
        ->add(new RequireRoleMiddleware(['patient']))
        ->add(AuthenticatedMiddleware::class)
        ->add(CreateRendezVousMiddleware::class);

    $app->delete('/rdv/{id}', AnnulerRdvAction::class)
        ->setName('rdv.cancel')
        ->add(CanCancelRdvMiddleware::class)
        ->add(new RequireRoleMiddleware(['admin', 'praticien', 'patient']))
        ->add(AuthenticatedMiddleware::class);

    $app->patch('/rdv/{id}', ModifierStatutRdvAction::class)
        ->setName('rdv.update_status')
        ->add(CanUpdateRdvStatusMiddleware::class)
        ->add(new RequireRoleMiddleware(['admin', 'praticien']))
        ->add(AuthenticatedMiddleware::class);

    return $app;
};
