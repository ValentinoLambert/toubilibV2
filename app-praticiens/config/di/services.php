<?php

use Psr\Container\ContainerInterface;
use toubilib\core\application\ports\PraticienRepositoryInterface;
use toubilib\infra\repositories\PDOPraticienRepository;
use toubilib\core\application\usecases\ServicePraticienInterface;
use toubilib\core\application\usecases\ServicePraticien;
use toubilib\core\application\ports\UserRepositoryInterface;
use toubilib\infra\repositories\PDOUserRepository;
use toubilib\core\application\usecases\AuthorizationServiceInterface;
use toubilib\core\application\usecases\AuthorizationService;
use toubilib\core\application\usecases\ServiceAuthInterface;
use toubilib\core\application\usecases\ServiceAuth;
use toubilib\core\application\ports\IndisponibiliteRepositoryInterface;
use toubilib\infra\repositories\PDOIndisponibiliteRepository;
use toubilib\core\application\usecases\ServiceIndisponibiliteInterface;
use toubilib\core\application\usecases\ServiceIndisponibilite;

return [
    // PDO connection factory
    PDO::class => function (ContainerInterface $c): PDO {
        $cfg = $c->get('db.prat');
        $dsn = sprintf('pgsql:host=%s;port=%d;dbname=%s', $cfg['host'], $cfg['port'], $cfg['name']);
        return new PDO($dsn, $cfg['user'], $cfg['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    },

    // Repository factory
    PraticienRepositoryInterface::class => function (ContainerInterface $c): PraticienRepositoryInterface {
        return new PDOPraticienRepository($c->get(PDO::class));
    },

    // Service factory
    ServicePraticienInterface::class => function (ContainerInterface $c): ServicePraticienInterface {
        return new ServicePraticien($c->get(PraticienRepositoryInterface::class));
    },

    // Auth PDO connection
    'pdo.auth' => function (ContainerInterface $c): PDO {
        $cfg = $c->get('db.auth');
        $dsn = sprintf('pgsql:host=%s;port=%d;dbname=%s', $cfg['host'], $cfg['port'], $cfg['name']);
        return new PDO($dsn, $cfg['user'], $cfg['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    },

    // Auth repository et services
    UserRepositoryInterface::class => function (ContainerInterface $c): UserRepositoryInterface {
        return new PDOUserRepository($c->get('pdo.auth'));
    },
    ServiceAuthInterface::class => function (ContainerInterface $c): ServiceAuthInterface {
        return new ServiceAuth($c->get(UserRepositoryInterface::class));
    },
    AuthorizationServiceInterface::class => function (ContainerInterface $c): AuthorizationServiceInterface {
        return new AuthorizationService();
    },

    // Indisponibilités
    IndisponibiliteRepositoryInterface::class => function (ContainerInterface $c): IndisponibiliteRepositoryInterface {
        return new PDOIndisponibiliteRepository($c->get(PDO::class));
    },
    ServiceIndisponibiliteInterface::class => function (ContainerInterface $c): ServiceIndisponibiliteInterface {
        return new ServiceIndisponibilite(
            $c->get(IndisponibiliteRepositoryInterface::class),
            $c->get(PraticienRepositoryInterface::class)
        );
    },
];
