<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use GuzzleHttp\Client;
use toubilib\gateway\application\actions\ProxyPraticiensAction;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        Client::class => function (ContainerInterface $c) {
            return new Client([
                'base_uri' => 'http://api.toubilib', 
                'timeout'  => 5.0,
            ]);
        },
        'praticiens_client' => function (ContainerInterface $c) {
            return new Client([
                'base_uri' => 'http://app-praticiens',
                'timeout'  => 5.0,
            ]);
        },
        ProxyPraticiensAction::class => function (ContainerInterface $c) {
            return new ProxyPraticiensAction($c->get('praticiens_client'));
        },
    ]);
};
