<?php
declare(strict_types=1);

use Slim\App;
use toubilib\gateway\application\actions\GenericAction;
use toubilib\gateway\application\actions\ProxyAction;

use toubilib\gateway\application\middleware\GatewayAuthMiddleware;

return function (App $app) {
    // Routes d'authentification
    $app->post('/auth/register', ProxyAction::class);
    $app->post('/auth/signin', ProxyAction::class);
    $app->post('/auth/refresh', ProxyAction::class);

    // Routes publiques spécifiques
    $app->get('/praticiens/{id}', ProxyAction::class);
    $app->get('/praticiens/{id}/rdv', ProxyAction::class);
    
    // Routes protégées (Exercice 4)
    $app->group('', function($group) {
        $group->get('/praticiens/{id}/agenda', ProxyAction::class);
        $group->post('/rdv', ProxyAction::class);
        $group->get('/rdv/{id}', ProxyAction::class);
    })->add(new GatewayAuthMiddleware($app->getContainer()));

    // Catch-all pour les autres routes (publique par défaut, ou à gérer)
    $app->map(['GET', 'POST', 'PATCH', 'DELETE'], '/{routes:.+}', ProxyAction::class);
};
