<?php
declare(strict_types=1);

use Slim\App;
use toubilib\gateway\application\actions\GetPraticiensAction;

return function (App $app) {
    $app->get('/praticiens', GetPraticiensAction::class);
};
