<?php
/**
 * Created by PhpStorm.
 * User: vitalie
 * Date: 7/23/15
 * Time: 1:18 PM
 */

namespace REST\ControllerProvider;


use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;
use REST\Service\User;

class Rest implements ControllerProviderInterface {
    /**
     * Returns routes to connect to the given application.
     *
     * @param Application $app An Application instance
     *
     * @return ControllerCollection A ControllerCollection instance
     */
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        /**
         * Retrieve a list of registered userIds
         */
        $controllers->get('/user', function(Application $app){
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
        $controllers->get('/user/{userId}', function(Application $app, $userId){
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
        $controllers->post('/user', function(Application $app, Request $request){
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
        $controllers->put('/user/{userId}', function(Application $app, Request $request, $userId){
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
        $controllers->delete('/user/{userId}', function(Application $app, $userId){
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

        return $controllers;

    }


}