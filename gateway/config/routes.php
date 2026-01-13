<?php
declare(strict_types=1);

use Slim\App;
use toubilib\gateway\application\actions\ProxyAction;
use toubilib\gateway\application\actions\ProxyPraticiensAction;

return function (App $app) {
    $app->get('/praticiens[/{routes:.*}]', ProxyPraticiensAction::class);
    $app->get('/{routes:.+}', ProxyAction::class);
};
