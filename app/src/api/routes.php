<?php
declare(strict_types=1);

use toubilib\api\actions\auth\LoginAction;
use toubilib\api\actions\auth\RefreshAction;
use toubilib\api\actions\auth\MeAction;
use toubilib\api\actions\praticien\ListerPraticiensAction;
use toubilib\api\actions\praticien\AfficherPraticienAction;
use toubilib\api\actions\praticien\ListerCreneauxOccupesAction;
use toubilib\api\actions\praticien\ListerAgendaAction;
use toubilib\api\actions\praticien\ListerIndisponibilitesAction;
use toubilib\api\actions\praticien\CreerIndisponibiliteAction;
use toubilib\api\actions\praticien\SupprimerIndisponibiliteAction;
use toubilib\api\actions\patient\ListerHistoriquePatientAction;
use toubilib\api\actions\patient\InscrirePatientAction;
use toubilib\api\actions\rdv\ConsulterRdvAction;
use toubilib\api\actions\rdv\CreerRdvAction;
use toubilib\api\actions\rdv\AnnulerRdvAction;
use toubilib\api\actions\rdv\ModifierStatutRdvAction;
use toubilib\api\middlewares\AuthenticatedMiddleware;
use toubilib\api\middlewares\CanAccessAgendaMiddleware;
use toubilib\api\middlewares\CanCancelRdvMiddleware;
use toubilib\api\middlewares\CanCreateRdvMiddleware;
use toubilib\api\middlewares\CanManageIndisponibiliteMiddleware;
use toubilib\api\middlewares\CanUpdateRdvStatusMiddleware;
use toubilib\api\middlewares\CanViewPatientHistoryMiddleware;
use toubilib\api\middlewares\CanViewRdvMiddleware;
use toubilib\api\middlewares\CreateRendezVousMiddleware;
use toubilib\api\middlewares\CreateIndisponibiliteMiddleware;
use toubilib\api\middlewares\InscriptionPatientMiddleware;
use toubilib\api\middlewares\OptionalAuthMiddleware;
use toubilib\api\middlewares\RequireRoleMiddleware;

return function( \Slim\App $app):\Slim\App {
    $app->post('/patients', InscrirePatientAction::class)
        ->setName('patients.inscrire')
        ->add(InscriptionPatientMiddleware::class);

    $app->get('/patients/{id}/historique', ListerHistoriquePatientAction::class)
        ->setName('patients.historique')
        ->add(CanViewPatientHistoryMiddleware::class)
        ->add(new RequireRoleMiddleware(['patient', 'admin']))
        ->add(AuthenticatedMiddleware::class);

    $app->get('/praticiens', ListerPraticiensAction::class)
        ->setName('praticiens.list')
        ->add(OptionalAuthMiddleware::class);

    $app->get('/praticiens/{id}', AfficherPraticienAction::class)
        ->setName('praticiens.detail')
        ->add(OptionalAuthMiddleware::class);

    $app->get('/praticiens/{id}/rdv/occupes', ListerCreneauxOccupesAction::class)
        ->setName('praticiens.rdv_occupes')
        ->add(OptionalAuthMiddleware::class);

    $app->get('/praticiens/{id}/agenda', ListerAgendaAction::class)
        ->setName('praticiens.agenda')
        ->add(CanAccessAgendaMiddleware::class)
        ->add(AuthenticatedMiddleware::class);

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

    $app->get('/rdv/{id}', ConsulterRdvAction::class)
        ->setName('rdv.detail')
        ->add(CanViewRdvMiddleware::class)
        ->add(AuthenticatedMiddleware::class);

    $app->post('/auth/login', LoginAction::class)->setName('auth.login');
    // Aliases for Gateway compliance
    $app->post('/auth/signin', LoginAction::class)->setName('auth.signin');
    $app->post('/auth/register', InscrirePatientAction::class)->setName('auth.register');
    $app->post('/auth/refresh', RefreshAction::class)->setName('auth.refresh');

    $app->get('/auth/me', MeAction::class)
        ->add(new RequireRoleMiddleware(['patient']))
        ->add(AuthenticatedMiddleware::class)
        ->add(CreateRendezVousMiddleware::class);

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
