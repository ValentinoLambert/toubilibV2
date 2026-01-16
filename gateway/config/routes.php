<?php
declare(strict_types=1);

use Slim\App;
use toubilib\gateway\application\actions\ProxyAction;
use toubilib\gateway\application\actions\ProxyPraticiensAction;
use toubilib\gateway\application\actions\ProxyRdvAction;

return function (App $app) {
    $app->get('/praticiens/{id}/agenda', ProxyRdvAction::class);
    $app->get('/praticiens/{id}/rdv/occupes', ProxyRdvAction::class);
    $app->map(['GET', 'POST', 'PATCH', 'DELETE'], '/rdv[/{routes:.*}]', ProxyRdvAction::class);
    $app->get('/praticiens[/{routes:.*}]', ProxyPraticiensAction::class);
    $app->map(['GET', 'POST', 'PATCH', 'DELETE'], '/{routes:.+}', ProxyAction::class);
};
