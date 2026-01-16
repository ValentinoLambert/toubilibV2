<?php

use Psr\Container\ContainerInterface;
use toubilib\api\actions\auth\LoginAction;
use toubilib\api\actions\auth\MeAction;
use toubilib\api\actions\praticien\ListerPraticiensAction;
use toubilib\core\application\usecases\ServicePraticienInterface;
use toubilib\api\actions\praticien\AfficherPraticienAction;
use toubilib\api\actions\praticien\ListerIndisponibilitesAction;
use toubilib\api\actions\praticien\CreerIndisponibiliteAction;
use toubilib\api\actions\praticien\SupprimerIndisponibiliteAction;
use toubilib\api\middlewares\AuthenticatedMiddleware;
use toubilib\api\middlewares\CanManageIndisponibiliteMiddleware;
use toubilib\api\middlewares\CorsMiddleware;
use toubilib\api\middlewares\CreateIndisponibiliteMiddleware;
use toubilib\api\middlewares\OptionalAuthMiddleware;
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
    CreateIndisponibiliteMiddleware::class => function (): CreateIndisponibiliteMiddleware {
        return new CreateIndisponibiliteMiddleware();
    },
    CanManageIndisponibiliteMiddleware::class => function (ContainerInterface $c): CanManageIndisponibiliteMiddleware {
        return new CanManageIndisponibiliteMiddleware($c->get(AuthorizationServiceInterface::class));
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
            $c->get(AuthProviderInterface::class),
            (string)$c->get('internal_token')
        );
    },
    OptionalAuthMiddleware::class => function (ContainerInterface $c): OptionalAuthMiddleware {
        return new OptionalAuthMiddleware($c->get(AuthProviderInterface::class));
    },
    CorsMiddleware::class => function (ContainerInterface $c): CorsMiddleware {
        return new CorsMiddleware($c->get('cors'));
    },
];
