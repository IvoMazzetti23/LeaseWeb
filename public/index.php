<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Http\Request;
use App\Router;

$request = Request::fromGlobals();
$router = new Router();

$router->get('/api/servers', [App\Controller\ServerController::class, 'index']);

$response = $router->dispatch($request);
$response->send();
