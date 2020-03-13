<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../includes/DbOperations.php';

$app = AppFactory::create();

$app->addRoutingMiddleware();



$app->setBasePath("/MyApi/public");

/**
 * endpoint: createuser
 * parametter: email, password, name, school
 * method: POST
 */
$app->post('/createuser',function(Request $request, Response $response){
	if(!haveEmptyParameters(array('email', 'password', 'name', 'school'),$request, $response)){
		$request_data = $request->getParsedBody();
	
		$email = $request_data['email'];
		$password = $request_data['password'];
		$name = $request_data['name'];
		$school = $request_data['school'];

		$hash_password = password_hash($password, PASSWORD_DEFAULT);

		$db = new DbOperations;
		$result = $db->createUser($email, $hash_password, $name, $school);
		if($result == USER_CREATED){
			$message = array();
			$message['error'] = false;
			$message['message'] = 'User created Successfully';
			$response->getBody()->write(json_encode($message));

			return $response
				->withHeader('Content-type', 'application/json')
				->withStatus(201);
		}
		else if($result == USER_FAILURE){
			$message = array();
			$message['error'] = true;
			$message['message'] = 'Some error occurred';
			$response->getBody()->write(json_encode($message));

			return $response
				->withHeader('Content-type', 'application/json')
				->withStatus(422);
		}
		else if($result == USER_EXISTS){
			$message = array();
			$message['error'] = true;
			$message['message'] = 'User Already Exists';
			$response->getBody()->write(json_encode($message));

			return $response
				->withHeader('Content-type', 'application/json')
				->withStatus(422);
		}
	}
	
	return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(422);  
});

$app->post('/userlogin',function(Request $request, Response $response){
	if(!haveEmptyParameters(array("email", "password"), $request, $response)){
		$request_data = $request->getParsedBody();
	
		$email = $request_data['email'];
		$password = $request_data['password'];
		$db = new DbOperations;

		$result = $db->userLogin($email, $password);
		if($result == USER_AUTHENTICATED){
			$user = $db->getUserByEmail($email);
			$message = array();
			$message['error'] =  false;
			$message['message'] = 'Login successful';
			$message['user'] = $user;

			$response->getBody()->write(json_encode($message));
			return $response
				->withHeader('Content-type', 'application/json')
				->withStatus(200);
		}
		else if($result == USER_NOT_FOUND){
			
			$message = array();
			$message['error'] =  true;
			$message['message'] = 'User not exist';
			

			$response->getBody()->write(json_encode($message));
			return $response
				->withHeader('Content-type', 'application/json')
				->withStatus(404);
		}
		else if($result == USER_PASSWORD_DO_NOT_MATCH){
			$message = array();
			$message['error'] =  true;
			$message['message'] = 'Invalid credential ';
			

			$response->getBody()->write(json_encode($message));
			return $response
				->withHeader('Content-type', 'application/json')
				->withStatus(404);
		}
	}
	return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(422);  

});

function haveEmptyParameters($require_params, $request, $response){
	$error = false;
	$error_params = '';
	$request_params = $request->getParsedBody();

	foreach($require_params as $param){
		if(!isset($request_params[$param]) || strlen($request_params[$param]) <= 0){
			$error = true;
			$error_params .= $param .', ';
		}
	}

	if($error){
		$error_detail = array();
		$error_detail['error'] = true;
		$error_detail['message'] = 'Required parameters ' . substr($error_params, 0, -2) . ' are missing or empty';
		$response->getBody()->write(json_encode($error_detail));
	}
	return $error; 
	
}


$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$app->run();
