<?php

use GuzzleHttp\Client;
use Psr\Container\ContainerInterface;
use toubilib\core\application\ports\IndisponibiliteRepositoryInterface;
use toubilib\core\application\ports\PatientRepositoryInterface;
use toubilib\core\application\ports\PraticienRepositoryInterface;
use toubilib\core\application\ports\RdvRepositoryInterface;
use toubilib\core\application\ports\UserRepositoryInterface;
use toubilib\core\application\usecases\AuthorizationService;
use toubilib\core\application\usecases\AuthorizationServiceInterface;
use toubilib\core\application\usecases\ServiceAuth;
use toubilib\core\application\usecases\ServiceAuthInterface;
use toubilib\core\application\usecases\ServiceRDV;
use toubilib\core\application\usecases\ServiceRDVInterface;
use toubilib\infra\gateways\IndisponibiliteApiRepository;
use toubilib\infra\gateways\PraticienApiRepository;
use toubilib\infra\repositories\PDOPatientRepository;
use toubilib\infra\repositories\PDORdvRepository;
use toubilib\infra\repositories\PDOUserRepository;

return [
    Client::class => function (ContainerInterface $c): Client {
        $headers = [];
        $token = (string)$c->get('internal_token');
        if ($token !== '') {
            $headers['X-Internal-Token'] = $token;
        }

        return new Client([
            'base_uri' => $c->get('praticiens.api_base_uri'),
            'timeout' => $c->get('praticiens.api_timeout'),
            'headers' => $headers,
            'http_errors' => false,
        ]);
    },

    // RDV PDO connection
    'pdo.rdv' => function (ContainerInterface $c): PDO {
        $cfg = $c->get('db.rdv');
        $dsn = sprintf('pgsql:host=%s;port=%d;dbname=%s', $cfg['host'], $cfg['port'], $cfg['name']);
        return new PDO($dsn, $cfg['user'], $cfg['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    },

    // Patient PDO connection
    'pdo.pat' => function (ContainerInterface $c): PDO {
        $cfg = $c->get('db.pat');
        $dsn = sprintf('pgsql:host=%s;port=%d;dbname=%s', $cfg['host'], $cfg['port'], $cfg['name']);
        return new PDO($dsn, $cfg['user'], $cfg['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    },

    // RDV repository and service
    RdvRepositoryInterface::class => function (ContainerInterface $c): RdvRepositoryInterface {
        return new PDORdvRepository($c->get('pdo.rdv'));
    },
    PatientRepositoryInterface::class => function (ContainerInterface $c): PatientRepositoryInterface {
        return new PDOPatientRepository($c->get('pdo.pat'));
    },
    PraticienRepositoryInterface::class => function (ContainerInterface $c): PraticienRepositoryInterface {
        return new PraticienApiRepository($c->get(Client::class));
    },
    IndisponibiliteRepositoryInterface::class => function (ContainerInterface $c): IndisponibiliteRepositoryInterface {
        return new IndisponibiliteApiRepository($c->get(Client::class));
    },
    ServiceRDVInterface::class => function (ContainerInterface $c): ServiceRDVInterface {
        return new ServiceRDV(
            $c->get(RdvRepositoryInterface::class),
            $c->get(PraticienRepositoryInterface::class),
            $c->get(PatientRepositoryInterface::class),
            $c->get(IndisponibiliteRepositoryInterface::class)
        );
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
        return new AuthorizationService($c->get(ServiceRDVInterface::class));
    },
];
