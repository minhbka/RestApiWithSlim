<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../includes/DbConnect.php';

$app = AppFactory::create();
$app->setBasePath("/MyApi/public");
$app->get('/hello/{name}', function (Request $request, Response $response, array $args) {
    $name = $args['name'];
	$response->getBody()->write("Hello, $name");
	$db = new DbConnect;

	if($db->connect() != null){
		echo 'Connection Successful'.'<br>';
	}

    return $response;
});

$app->run();
