<?php
require_once __DIR__.'/vendor/autoload.php';
use Silex\Application;
use REST\Service\User;
use Symfony\Component\HttpFoundation\Response;


$app = new Application();

$app->mount('/api/', new \REST\ControllerProvider\User());

$app->register(new JDesrosiers\Silex\Provider\JsonSchemaServiceProvider());
$app["json-schema.schema-store"]->add("user_create_schema", json_decode(file_get_contents(__DIR__ . '/schema/user_create.json')));
$app["json-schema.schema-store"]->add("user_update_schema", json_decode(file_get_contents(__DIR__ . '/schema/user_update.json')));

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

$app['userService'] = function(Application $app){
    return new User($app['db']);
};

$app->error(function (\Exception $e, $code) {
    $responseBody = [
        'class' => 'error',
        'properties' => [
            'message' => $e->getMessage()
        ]
    ];
    return new Response(json_encode($responseBody), $code, ['Content-Type' => 'application/json']);
});

return $app;