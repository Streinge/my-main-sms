<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

$app->get('localhost/index.php/special/send', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Hello world!");
    return $response;
});

$app->post('/special/send', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Other Hello world!");
    //$response->getBody()->getContents();
    var_dump($response);
    return $response;
});


$app->run();