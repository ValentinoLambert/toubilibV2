<?php

use Psr\Container\ContainerInterface;
use toubilib\api\actions\auth\LoginAction;
use toubilib\api\actions\auth\MeAction;
use toubilib\api\actions\praticien\ListerPraticiensAction;
use toubilib\core\application\usecases\ServicePraticienInterface;
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
use toubilib\api\middlewares\AuthorizationMiddleware;
use toubilib\api\middlewares\CorsMiddleware;
use toubilib\api\middlewares\CreateRendezVousMiddleware;
use toubilib\api\middlewares\CreateIndisponibiliteMiddleware;
use toubilib\api\middlewares\InscriptionPatientMiddleware;
use toubilib\api\middlewares\OptionalAuthMiddleware;
use toubilib\core\application\usecases\ServiceRDVInterface;
use toubilib\core\application\usecases\ServicePatientInterface;
use toubilib\core\application\usecases\ServiceIndisponibiliteInterface;
use toubilib\api\provider\AuthProviderInterface;
use toubilib\api\provider\AuthProvider;
use toubilib\api\security\JwtManagerInterface;
use toubilib\api\security\JwtManager;
use toubilib\core\application\usecases\AuthorizationServiceInterface;
use toubilib\core\application\usecases\ServiceAuthInterface;

return [
    LoginAction::class => function (ContainerInterface $c): LoginAction {
        return new LoginAction($c->get(AuthProviderInterface::class));
    },
    MeAction::class => function (): MeAction {
        return new MeAction();
    },
    ListerPraticiensAction::class => function (ContainerInterface $c): ListerPraticiensAction {
        return new ListerPraticiensAction($c->get(ServicePraticienInterface::class));
    },
    AfficherPraticienAction::class => function (ContainerInterface $c): AfficherPraticienAction {
        return new AfficherPraticienAction($c->get(ServicePraticienInterface::class));
    },
    ListerCreneauxOccupesAction::class => function (ContainerInterface $c): ListerCreneauxOccupesAction {
        return new ListerCreneauxOccupesAction($c->get(ServiceRDVInterface::class));
    },
    ConsulterRdvAction::class => function (ContainerInterface $c): ConsulterRdvAction {
        return new ConsulterRdvAction($c->get(ServiceRDVInterface::class));
    },
    ListerAgendaAction::class => function (ContainerInterface $c): ListerAgendaAction {
        return new ListerAgendaAction($c->get(ServiceRDVInterface::class));
    },
    CreerRdvAction::class => function (ContainerInterface $c): CreerRdvAction {
        return new CreerRdvAction(
            $c->get(ServiceRDVInterface::class),
            $c->get(AuthorizationServiceInterface::class)
        );
    },
    AnnulerRdvAction::class => function (ContainerInterface $c): AnnulerRdvAction {
        return new AnnulerRdvAction(
            $c->get(ServiceRDVInterface::class),
            $c->get(AuthorizationServiceInterface::class)
        );
    },
    ModifierStatutRdvAction::class => function (ContainerInterface $c): ModifierStatutRdvAction {
        return new ModifierStatutRdvAction($c->get(ServiceRDVInterface::class));
    },
    CreateRendezVousMiddleware::class => function (): CreateRendezVousMiddleware {
        return new CreateRendezVousMiddleware();
    },
    CreateIndisponibiliteMiddleware::class => function (): CreateIndisponibiliteMiddleware {
        return new CreateIndisponibiliteMiddleware();
    },
    InscriptionPatientMiddleware::class => function (): InscriptionPatientMiddleware {
        return new InscriptionPatientMiddleware();
    },
    ListerHistoriquePatientAction::class => function (ContainerInterface $c): ListerHistoriquePatientAction {
        return new ListerHistoriquePatientAction($c->get(ServicePatientInterface::class));
    },
    InscrirePatientAction::class => function (ContainerInterface $c): InscrirePatientAction {
        return new InscrirePatientAction(
            $c->get(ServicePatientInterface::class),
            $c->get(AuthProviderInterface::class)
        );
    },
    ListerIndisponibilitesAction::class => function (ContainerInterface $c): ListerIndisponibilitesAction {
        return new ListerIndisponibilitesAction($c->get(ServiceIndisponibiliteInterface::class));
    },
    CreerIndisponibiliteAction::class => function (ContainerInterface $c): CreerIndisponibiliteAction {
        return new CreerIndisponibiliteAction($c->get(ServiceIndisponibiliteInterface::class));
    },
    SupprimerIndisponibiliteAction::class => function (ContainerInterface $c): SupprimerIndisponibiliteAction {
        return new SupprimerIndisponibiliteAction($c->get(ServiceIndisponibiliteInterface::class));
    },
    JwtManagerInterface::class => function (ContainerInterface $c): JwtManagerInterface {
        return new JwtManager(
            $c->get('auth.jwt.secret'),
            $c->get('auth.jwt.expiration'),
            $c->get('auth.jwt.refresh_expiration'),
            $c->get('auth.jwt.issuer')
        );
    },
    AuthProviderInterface::class => function (ContainerInterface $c): AuthProviderInterface {
        return new AuthProvider(
            $c->get(ServiceAuthInterface::class),
            $c->get(JwtManagerInterface::class)
        );
    },
    AuthenticatedMiddleware::class => function (ContainerInterface $c): AuthenticatedMiddleware {
        return new AuthenticatedMiddleware(
            $c->get(JwtManagerInterface::class),
            $c->get(ServiceAuthInterface::class)
        );
    },
    OptionalAuthMiddleware::class => function (ContainerInterface $c): OptionalAuthMiddleware {
        return new OptionalAuthMiddleware(
            $c->get(JwtManagerInterface::class),
            $c->get(ServiceAuthInterface::class)
        );
    },
    AuthorizationMiddleware::class => function (ContainerInterface $c): AuthorizationMiddleware {
        return new AuthorizationMiddleware($c->get(AuthorizationServiceInterface::class));
    },
    CorsMiddleware::class => function (ContainerInterface $c): CorsMiddleware {
        return new CorsMiddleware($c->get('cors'));
    },
];
