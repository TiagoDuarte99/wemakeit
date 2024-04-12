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

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UsersController;

$router->get('/', function () use ($router) {
/*     if (extension_loaded('imagick')) {
        echo 'Extensão Imagick está carregada.';
    } else {
        echo 'Extensão Imagick não está carregada.';
    }
    if (class_exists('Imagick')) {
        echo 'Classe Imagick está disponível.';
    } else {
        echo 'Classe Imagick não está disponível.';
    } */
    return app()->version();
});

$router->group(['prefix' => 'auths'], function () use ($router) {
    $router->post('login', 'AuthController@login');
});

$router->group(['prefix' => 'pages'], function () use ($router) {
    $router->get('{namePage}', 'PagesController@getPageData');
});

$router->group(['prefix' => 'auths', 'middleware' => 'auth'], function () use ($router) {
    $router->get('me', 'AuthController@me'); // retorna o utilizador com o login efectuado
    $router->get('logout', 'AuthController@logout'); // faz logout
});


$router->group(['prefix' => 'users', 'middleware' => 'auth'], function () use ($router) {
    $router->get('', 'UsersController@listUsers'); //retorna todos os utilizadores
    $router->post('register', 'UsersController@register'); //registar novos utilizadores apenas um utilizador registado consegue
    $router->put('/{id}', 'UsersController@update'); // Update user
    $router->delete('/{id}', 'UsersController@delete'); // delete user
});

$router->group(['prefix' => 'pages', 'middleware' => 'auth'], function () use ($router) {
    $router->post('insert', 'PagesController@insertPageData');
    $router->put('/{namePage}/{section}', 'PagesController@updatePage'); 
    $router->delete('/{namePage}', 'PagesController@deletePage');
    $router->delete('/{namePage}/{section}', 'PagesController@deleteSection');

});

$router->group(['prefix' => 'upload', 'middleware' => ['auth'/* , 'process.image' */]], function () use ($router) {
    $router->post('', 'UploadController@upload');
});

$router->group(['prefix' => 'sendEmail', 'middleware' => 'recaptcha'], function () use ($router) {
    $router->post('', 'EmailController@send');
});