<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use GuzzleHttp\Client;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        'auth.client' => function (ContainerInterface $c) {
            return new Client([
                'base_uri' => 'http://app.auth', 
                'timeout'  => 5.0,
            ]);
        },
        'api.client' => function (ContainerInterface $c) {
            return new Client([
                'base_uri' => 'http://api.toubilib', 
                'timeout'  => 5.0,
            ]);
        },
    ]);
};
