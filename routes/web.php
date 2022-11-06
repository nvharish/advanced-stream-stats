<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->post('/auth/token', 'AuthController@createToken');
$router->delete('/auth/token', 'AuthController@destroyToken');
$router->post('/subscription/purchase', 'SubscriptionController@purchase');
$router->get('/subscription/retrieve', 'SubscriptionController@retrieve');
$router->post('/subscription/authorize', 'SubscriptionController@authorizePayment');
$router->post('/subscription/cancel', 'SubscriptionController@cancel');
$router->post('/subscription/renew', 'SubscriptionController@renew');