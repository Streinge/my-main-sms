<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use SalesRender\Plugin\Instance\Chat\Components\MainSmsApi;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

$app->addRoutingMiddleware();

$errorMiddleware = $app->addErrorMiddleware(true, true, true);

$app->post('/special/send', function (Request $request, Response $response, $args) {
    $data = json_decode($request->getBody()->getContents(), true);
    $mainSmailApi = new MainSmsApi($data['apikey']);
    $mainSmsResponseBodyArray = $mainSmailApi->sendEmail(
        $data['sender'],
        $data['name'],
        $data['subject'],
        $data['recipient'],
        $data['text']
    );

    var_dump($mainSmsResponseBodyArray);
    if (!array_key_exists('errors', $mainSmsResponseBodyArray)) {
        return $response->withStatus(202);
    } else {
        return $response->withStatus(405);
    }

});

$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Hello world!");
    return $response;
});

$app->run();
