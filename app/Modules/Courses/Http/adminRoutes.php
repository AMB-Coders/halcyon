<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'courses'], function (Router $router)
{
	// Members
	$router->group(['prefix' => 'members'], function (Router $router)
	{
		$router->match(['get', 'post'], '/', [
			'as'   => 'admin.courses.members',
			'uses' => 'MembersController@index',
			'middleware' => 'can:manage courses',
		]);
		$router->get('/create', [
			'as' => 'admin.courses.members.create',
			'uses' => 'MembersController@create',
			'middleware' => 'can:create courses.members',
		]);
		$router->post('/store', [
			'as' => 'admin.courses.members.store',
			'uses' => 'MembersController@store',
			'middleware' => 'can:create courses.members,edit courses.members',
		]);
		$router->get('/edit/{id}', [
			'as' => 'admin.courses.members.edit',
			'uses' => 'MembersController@edit',
			'middleware' => 'can:edit courses.members',
		]);
		$router->match(['get', 'post'], '/delete/{id?}', [
			'as'   => 'admin.courses.members.delete',
			'uses' => 'MembersController@delete',
			'middleware' => 'can:delete courses.members',
		]);
		$router->post('/cancel', [
			'as' => 'admin.courses.members.cancel',
			'uses' => 'MembersController@cancel',
		]);
	});

	$router->match(['get', 'post'], '/', [
		'as' => 'admin.courses.index',
		'uses' => 'AccountsController@index',
		'middleware' => 'can:manage courses',
	]);
	$router->get('create', [
		'as' => 'admin.courses.create',
		'uses' => 'AccountsController@create',
		'middleware' => 'can:create courses',
	]);
	$router->post('store', [
		'as' => 'admin.courses.store',
		'uses' => 'AccountsController@store',
		'middleware' => 'can:create courses,edit courses',
	]);
	$router->get('{id}', [
		'as' => 'admin.courses.edit',
		'uses' => 'AccountsController@edit',
		'middleware' => 'can:edit courses',
	]);
	$router->match(['get', 'post'], '/delete/{id?}', [
		'as'   => 'admin.courses.delete',
		'uses' => 'AccountsController@delete',
		'middleware' => 'can:delete courses',
	]);
	$router->match(['get', 'post'], '/cancel', [
		'as'   => 'admin.courses.cancel',
		'uses' => 'AccountsController@cancel',
	]);
});
