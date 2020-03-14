<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../includes/DbOperations.php';

$app = AppFactory::create();

$app->addRoutingMiddleware();
$app->addBodyParsingMiddleware();



$app->setBasePath("/MyApi/public");

/**
 * endpoint: createuser
 * parametter: email, password, name, school
 * method: POST
 */
$app->post('/signup',function(Request $request, Response $response){
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
			$user = $db->getUserByEmail($email);
			$message = array();
			$message['isSuccessful'] = true;
			$message['message'] = 'User created Successfully';
			$message['user'] = $user;
			$response->getBody()->write(json_encode($message));

			return $response
				->withHeader('Content-type', 'application/json')
				->withStatus(201);
		}
		else if($result == USER_FAILURE){
			$message = array();
			$message['isSuccessful'] = false;
			$message['message'] = 'Some error occurred';
			$response->getBody()->write(json_encode($message));

			return $response
				->withHeader('Content-type', 'application/json')
				->withStatus(422);
		}
		else if($result == USER_EXISTS){
			$message = array();
			$message['isSuccessful'] = false;
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

$app->post('/login',function(Request $request, Response $response){
	if(!haveEmptyParameters(array("email", "password"), $request, $response)){
		$request_data = $request->getParsedBody();
	
		$email = $request_data['email'];
		$password = $request_data['password'];
		$db = new DbOperations;

		$result = $db->userLogin($email, $password);
		if($result == USER_AUTHENTICATED){
			$user = $db->getUserByEmail($email);
			$message = array();
			$message['isSuccessful'] =  true;
			$message['message'] = 'Login successful';
			$message['user'] = $user;

			$response->getBody()->write(json_encode($message));
			return $response
				->withHeader('Content-type', 'application/json')
				->withStatus(200);
		}
		else if($result == USER_NOT_FOUND){
			
			$message = array();
			$message['isSuccessful'] =  false;
			$message['message'] = 'User not exist';
			

			$response->getBody()->write(json_encode($message));
			return $response
				->withHeader('Content-type', 'application/json')
				->withStatus(404);
		}
		else if($result == USER_PASSWORD_DO_NOT_MATCH){
			$message = array();
			$message['isSuccessful'] =  false;
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


$app->get('/allusers', function(Request $request, Response $response){
	$db = new DbOperations;
	$users = $db->getAllUsers();
	$message = array();
	$message['isSuccessful'] =  true;
	$message['users'] = $users;
	$response->getBody()->write(json_encode($message));

	return $response
		->withHeader('Content-type', 'application/json')
		->withStatus(200);

});

$app->put('/updateuser/{id}', function(Request $request, Response $response, array $args){
	$id = $args['id'];
	if(!haveEmptyParameters(array("email", "name", "school"), $request, $response)){
		$request_data = $request->getParsedBody();
		$email = $request_data['email'];
		$name = $request_data['name'];
		$school = $request_data['school'];
		
		$db = new DbOperations;
		$result = $db->updateUser($id, $email, $name, $school);
		if($result){
			$user = $db->getUserByEmail($email);
			$message = array();
			$message['isSuccessful'] = true;
			$message['message'] = 'User updated Successfully';
			$message['user'] = $user;


			$response->getBody()->write(json_encode($message));
			return $response
				->withHeader('Content-type', 'application/json')
				->withStatus(200);
		}
		else{
			$message = array();
			$message['isSuccessful'] = false;
			$message['message'] = 'Please try again latter';
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
$app->put('/updatepassword', function(Request $request, Response $response){
	
	if(!haveEmptyParameters(array("currentpassword", "newpassword", "email"), $request, $response)){

		$request_data = $request->getParsedBody();
		$currentpassword = $request_data['currentpassword'];
		$newpassword = $request_data['newpassword'];
		$email = $request_data['email'];
		$db = new DbOperations;
		$result = $db->updatePassword($email, $currentpassword, $newpassword);

		if($result == PASSWORD_CHANGED){
			
			$message = array();
			$message['isSuccessful'] = true;
			$message['message'] = 'password changed Successfully';
			$response->getBody()->write(json_encode($message));
			return $response
				->withHeader('Content-type', 'application/json')
				->withStatus(200);
		}
		else if($result == PASSWORD_NOT_CHANGED){
			$message = array();
			$message['isSuccessful'] = false;
			$message['message'] = 'Some errors orrcured, Please try again latter';
			$response->getBody()->write(json_encode($message));

			return $response
				->withHeader('Content-type', 'application/json')
				->withStatus(404);
		}
		else if($result == PASSWORD_DO_NOT_MATCH){
			$message = array();
			$message['isSuccessful'] =  false;
			$message['message'] = 'Password do not matched';
			

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

$app -> delete('/deleteuser/{id}', function(Request $request, Response $response, $args){
	$id = $args['id'];
	$db = new DbOperations;

	if($db->deleteUser($id)){
		$message = array();
		$message['isSuccessful'] = true;
		$message['message'] = 'user has been deleted ';
		$response->getBody()->write(json_encode($message));
		return $response
			->withHeader('Content-type', 'application/json')
			->withStatus(200);
	}
	else{
		$message = array();
		$message['isSuccessful'] =  false;
		$message['message'] = 'Please try again later';
		

		$response->getBody()->write(json_encode($message));
		return $response
			->withHeader('Content-type', 'application/json')
			->withStatus(404);
	}

});

$app -> get('/quotes', function(Request $request, Response $response){
	$db = new DbOperations;
	$quotes = $db->getAllQuotes();
	$message = array();
	$message['isSuccessful'] =  true;
	$message['quotes'] = $quotes;
	$response->getBody()->write(json_encode($message));

	return $response
		->withHeader('Content-type', 'application/json')
		->withStatus(200);
});

$app -> get('/movies', function(Request $request, Response $response){
	$db = new DbOperations;
	$movies = $db->getAllMovies();
	$message = array();
	$message['isSuccessful'] =  true;
	$message['movies'] = $movies;
	$response->getBody()->write(json_encode($message));

	return $response
		->withHeader('Content-type', 'application/json')
		->withStatus(200);
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
		$error_detail['isSuccessful'] = false;
		$error_detail['message'] = 'Required parameters ' . substr($error_params, 0, -2) . ' are missing or empty';
		$response->getBody()->write(json_encode($error_detail));
	}
	return $error; 
	
}


$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$app->run();
