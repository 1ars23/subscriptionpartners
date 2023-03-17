<?php

use App\Http\Middleware\JwtMiddleware;

$router->post('/subscribe', 'MerchantController@subscribe');
$router->post('/unsubscribe', 'MerchantController@unsubscribe');
// $router->post('/subscription-callback', 'PartnerController@notification');

$router->group(['middleware' => JwtMiddleware::class], function () use ($router) {
    $router->post('/subscribe/{jwtToken}', 'PartnerController@subscribe');
    $router->post('/unsubscribe/{jwtToken}', 'PartnerController@unsubscribe');
});


