<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->get('login', [
	'middleware' => 'cas.auth',
	'as'   => 'login',
	'uses' => 'AuthController@login'
]);
$router->post('login', [
	'as'   => 'login.post',
	'uses' => 'AuthController@authenticate'
]);
$router->get('callback', [
	//'middleware' => 'cas.guest',
	'as'   => 'callback',
	'uses' => 'AuthController@callback'
]);

if (config('user.allow_registration', true))
{
	$router->get('register', [
		'middleware' => 'auth.guest',
		'as'   => 'register',
		'uses' => 'AuthController@register'
	]);
	$router->post('register', [
		'as'   => 'register.post',
		'uses' => 'AuthController@registering'
	]);
}

$router->group(['prefix' => 'account', 'middleware' => 'auth.admin'], function (Router $router)
{
	$router->get('/', [
		'as' => 'site.users.account',
		'uses' => 'UsersController@profile',
	]);
	$router->get('myinfo', [
		'uses' => 'UsersController@profile',
	]);
	$router->get('request', [
		'as' => 'site.users.account.request',
		'uses' => 'UsersController@request',
	]);
	/*$router->get('quotas', [
		'as' => 'site.users.account.quotas',
		'uses' => 'UsersController@quotas',
	]);
	$router->get('myquotas', [
		'uses' => 'UsersController@quotas',
	]);
	$router->get('groups', [
		'as' => 'site.users.account.groups',
		'uses' => 'UsersController@groups',
	]);
	$router->get('group', [
		'uses' => 'UsersController@groups',
	]);
	$router->get('group/{group}', [
		'as' => 'site.users.account.group',
		'uses' => 'UsersController@group',
	]);*/
	$router->get('{section}', [
		'as' => 'site.users.account.section',
		'uses' => 'UsersController@profile',
	])->where('section', '[a-zA-Z0-9\-_]+');
	$router->get('{section}/{id}', [
		'as' => 'site.users.account.section.show',
		'uses' => 'UsersController@profile',
	])->where('section', '[a-zA-Z0-9]+')->where('id', '[0-9]+');
});

$router->get('reset', [
	'as' => 'reset',
	'uses' => 'AuthController@reset'
]);
$router->post('reset', [
	'as' => 'reset.post',
	'uses' => 'AuthController@resetting'
]);

// Account Activation
$router->get(
	'activate/{userId}/{activationCode}',
	'AuthController@activate'
);

$router->get('logout', [
	'as'   => 'logout',
	'uses' => 'AuthController@logout'
]);
