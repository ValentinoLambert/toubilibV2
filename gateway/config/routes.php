<?php
declare(strict_types=1);

use Slim\App;
use toubilib\gateway\application\actions\ProxyAction;

return function (App $app) {
    $app->map(['GET', 'POST', 'PATCH', 'DELETE'], '/{routes:.+}', ProxyAction::class);
};
