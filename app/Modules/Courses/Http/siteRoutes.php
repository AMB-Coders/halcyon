<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => 'courses'], function (Router $router)
{
	$router->post('/export', [
		'as' => 'site.courses.export',
		'uses' => 'AccountsController@export',
	]);

	$router->post('/import', [
		'as' => 'site.courses.import',
		'uses' => 'AccountsController@import',
	]);
});