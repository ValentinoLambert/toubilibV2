<?php

use Psr\Container\ContainerInterface;
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
use toubilib\api\middlewares\CorsMiddleware;
use toubilib\api\middlewares\CreateRendezVousMiddleware;
use toubilib\api\middlewares\OptionalAuthMiddleware;
use toubilib\core\application\usecases\ServiceRDVInterface;
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
        return new CreerRdvAction($c->get(ServiceRDVInterface::class));
    },
    AnnulerRdvAction::class => function (ContainerInterface $c): AnnulerRdvAction {
        return new AnnulerRdvAction($c->get(ServiceRDVInterface::class));
    },
    ModifierStatutRdvAction::class => function (ContainerInterface $c): ModifierStatutRdvAction {
        return new ModifierStatutRdvAction($c->get(ServiceRDVInterface::class));
    },
    CreateRendezVousMiddleware::class => function (): CreateRendezVousMiddleware {
        return new CreateRendezVousMiddleware();
    },
    CanAccessAgendaMiddleware::class => function (ContainerInterface $c): CanAccessAgendaMiddleware {
        return new CanAccessAgendaMiddleware($c->get(AuthorizationServiceInterface::class));
    },
    CanViewRdvMiddleware::class => function (ContainerInterface $c): CanViewRdvMiddleware {
        return new CanViewRdvMiddleware($c->get(AuthorizationServiceInterface::class));
    },
    CanCreateRdvMiddleware::class => function (ContainerInterface $c): CanCreateRdvMiddleware {
        return new CanCreateRdvMiddleware($c->get(AuthorizationServiceInterface::class));
    },
    CanCancelRdvMiddleware::class => function (ContainerInterface $c): CanCancelRdvMiddleware {
        return new CanCancelRdvMiddleware($c->get(AuthorizationServiceInterface::class));
    },
    CanUpdateRdvStatusMiddleware::class => function (ContainerInterface $c): CanUpdateRdvStatusMiddleware {
        return new CanUpdateRdvStatusMiddleware($c->get(AuthorizationServiceInterface::class));
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
        return new AuthenticatedMiddleware($c->get(AuthProviderInterface::class));
    },
    OptionalAuthMiddleware::class => function (ContainerInterface $c): OptionalAuthMiddleware {
        return new OptionalAuthMiddleware($c->get(AuthProviderInterface::class));
    },
    CorsMiddleware::class => function (ContainerInterface $c): CorsMiddleware {
        return new CorsMiddleware($c->get('cors'));
    },
];
