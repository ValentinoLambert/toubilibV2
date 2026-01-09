<?php
declare(strict_types=1);

use Slim\App;
use toubilib\gateway\application\actions\GetPraticiensAction;
use toubilib\gateway\application\actions\GetPraticienAction;

return function (App $app) {
    $app->get('/praticiens', GetPraticiensAction::class);
    $app->get('/praticiens/{id}', GetPraticienAction::class);
};
