<?php

namespace App\Modules\Storage\Providers;

use App\Providers\RoutingServiceProvider as CoreRoutingServiceProvider;

class RouteServiceProvider extends CoreRoutingServiceProvider
{
	/**
	 * The root namespace to assume when generating URLs to actions.
	 * @var string
	 */
	protected $namespace = 'App\Modules\Storage\Http\Controllers';

	/**
	 * @return string
	 */
	protected function getSiteRoute()
	{
		return dirname(__DIR__) . '/Routes/site.php';
	}

	/**
	 * @return string
	 */
	protected function getAdminRoute()
	{
		return dirname(__DIR__) . '/Routes/admin.php';
	}

	/**
	 * @return string
	 */
	protected function getApiRoute()
	{
		return dirname(__DIR__) . '/Routes/api.php';
	}

	/**
	 * // [!] Legacy compatibility
	 * 
	 * @return string
	 */
	protected function getWsRoute()
	{
		return dirname(__DIR__) . '/Routes/ws.php';
	}
}
