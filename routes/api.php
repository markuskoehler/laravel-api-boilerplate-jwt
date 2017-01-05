<?php

use Dingo\Api\Routing\Router;

/** @var Router $api */
$api = app(Router::class);

$api->version('v1', function (Router $api) {
    // auth routes
    $api->group(['prefix' => 'auth'], function(Router $api) {
        $api->post('signup', 'App\Api\V1\Controllers\SignUpController@signUp');
        $api->post('login', 'App\Api\V1\Controllers\LoginController@login');

        // todo not implemented yet
        //$api->post('recovery', 'App\Api\V1\Controllers\ForgotPasswordController@sendResetEmail');
        //$api->post('reset', 'App\Api\V1\Controllers\ResetPasswordController@resetPassword');
    });

    $api->group(['middleware' => 'jwt.auth'], function(Router $api) {
        $api->get('timediff', function() {
            $diff = (new \DateTime('2013-10-09 00:00:00'))->diff(new \DateTime());
            //$diff = (new \DateTime('@0'))->diff(new \DateTime('@102211200'));
            return response()->json([
                //'message' => 'Access to this item is only for authenticated user. Provide a token in your request!'
                /*"years" => (new \DateTime('@0'))->diff(new \DateTime('@102211200'))->y,
                "months" => (new \DateTime('@0'))->diff(new \DateTime('@102211200'))->m,
                "days" => (new \DateTime('@0'))->diff(new \DateTime('@102211200'))->d*/
                "years" => $diff->y,
                "months" => $diff->m,
                "days" => $diff->d
            ]);
        });

        $api->get('refresh', [
            'middleware' => 'jwt.refresh',
            function() {
                return response()->json([
                    'message' => 'By accessing this endpoint, you can refresh your access token at each request. Check out this response headers!'
                ]);
            }
        ]);
    });

    $api->get('hello', function() {
        return response()->json([
            'message' => 'This is a simple example of item returned by your APIs. Everyone can see it.'
        ]);
    });
});
