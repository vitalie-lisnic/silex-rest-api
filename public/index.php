<?php
require_once __DIR__.'/../vendor/autoload.php';
use Silex\Application;
use PDO;
use REST\Service\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;

$app = new Application();

$app['db'] = function(){
    return new PDO(
        "mysql:dbname=RestAPI;host=mysql;encoding=UTF8",
        "root",
        "root",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
        ]
    );
};

$app->register(new JDesrosiers\Silex\Provider\JsonSchemaServiceProvider());


$app["json-schema.schema-store"]->add("user_create_schema", json_decode(file_get_contents(__DIR__ . '/../schema/user_create.json')));
$app["json-schema.schema-store"]->add("user_update_schema", json_decode(file_get_contents(__DIR__ . '/../schema/user_update.json')));


$app['userService'] = function(Application $app){
    return new \REST\Service\User($app['db']);
};

$app->error(function (\Exception $e, $code) {
    $responseBody = [
        'class' => 'error',
        'properties' => [
            'message' => $e->getMessage()
        ]
    ];
    return new Response(json_encode($responseBody), $code);
});

/**
 * Retrieve a list of registered userIds
 */
$app->get('/api/user', function(Application $app){
    /**
     * @var $userService User
     */
    $userService = $app['userService'];

    $responseBody = [
        'class' => 'collection',
        'properties' => $userService->getUserIdsList()
    ];

    return new Response(json_encode($responseBody), 200, ['Content-Type' => 'application/json']);
});

/**
 * Get list of userIds
 */
$app->get('/api/user/{userId}', function(Application $app, $userId){
    /**
     * @var $userService User
     */
    $userService = $app['userService'];
    $user = $userService->getUserById($userId);

    if(!$user) {
        throw new NotFoundHttpException("User [{$userId}] not found");
    }

    $responseBody = [
        'class' => 'user',
        'properties' => $user
    ];

    return new Response(json_encode($responseBody), 200, ['Content-Type' => 'application/json']);
});

/**
 * Create a new user
 */
$app->post('/api/user', function(Application $app, Request $request){
    $userRaw = $request->getContent();
    $decodedData = json_decode($userRaw);

    if(!$decodedData){
        throw new BadRequestHttpException("Request data could not be decoded");
    }

    $schema = $app["json-schema.schema-store"]->get("user_create_schema");
    $validation = $app["json-schema.validator"]->validate($decodedData, $schema);


    if (!$validation->valid) {
        $errorMessages = implode(';', array_map(function($err){return $err->dataPath . ':' . $err->getMessage();}, $validation->errors));
        throw new PreconditionFailedHttpException($errorMessages);
    }

    /**
     * @var $userService User
     */
    $userService = $app['userService'];
    $userId = $userService->createUser((array) $decodedData->properties);

    $responseBody = [
        'class' => 'user',
        'properties' => $userService->getUserById($userId)
    ];

    return new Response(json_encode($responseBody), 200, ['Content-Type' => 'application/json']);
});

/**
 * Update user
 */
$app->put('/api/user/{userId}', function(Application $app, Request $request, $userId){
    $userRaw = $request->getContent();
    $decodedData = json_decode($userRaw);

    if(!$decodedData){
        throw new BadRequestHttpException("Request data could not be decoded");
    }

    $schema = $app["json-schema.schema-store"]->get("user_update_schema");
    $validation = $app["json-schema.validator"]->validate($decodedData, $schema);

    if (!$validation->valid) {
        $errorMessages = implode(';', array_map(function($err){return $err->dataPath . ':' . $err->getMessage();}, $validation->errors));
        throw new PreconditionFailedHttpException($errorMessages);
    }

    /**
     * @var $userService User
     */
    $userService = $app['userService'];
    $user = $userService->getUserById($userId);

    if(!$user) {
        throw new NotFoundHttpException("User [{$userId}] not found");
    }

    $userService->updateUser($userId, (array) $decodedData->properties);

    $responseBody = [
        'class' => 'user',
        'properties' => $userService->getUserById($userId)
    ];

    return new Response(json_encode($responseBody), 200, ['Content-Type' => 'application/json']);
});

/*
 * Delete user
 */
$app->delete('/api/user/{userId}', function(Application $app, $userId){
    /**
     * @var $userService User
     */
    $userService = $app['userService'];
    $user = $userService->getUserById($userId);

    if(!$user) {
        throw new NotFoundHttpException("User [{$userId}] not found");
    }

    $userService->removeUserById($userId);

    $responseBody = [
        'class' => 'user',
        'properties' => $user
    ];

    return new Response(json_encode($responseBody), 200, ['Content-Type' => 'application/json']);

});

$app->run();