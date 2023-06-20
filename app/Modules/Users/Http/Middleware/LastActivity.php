<?php

namespace App\Modules\Users\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Http\Request;

class LastActivity
{
	/**
	 * The authentication factory instance.
	 *
	 * @var Auth
	 */
	protected $auth;

	/**
	 * Create a new middleware instance.
	 *
	 * @param   Auth  $auth
	 * @return  void
	 */
	public function __construct(Auth $auth)
	{
		$this->auth = $auth;
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param   equest  $request
	 * @param   Closure  $next
	 * @return  mixed
	 */
	public function handle(Request $request, Closure $next)
	{
		if ($this->auth->check()
		 && $this->auth->user()->last_visit < Carbon::now()->subMinutes(5)->toDateTimeString())
		{
			$user = $this->auth->user();
			//$user->update(['last_visit' => Carbon::now()->toDateTimeString()]);
			$user->getUserUsername()->datelastseen = Carbon::now()->toDateTimeString();
			$user->getUserUsername()->save();
		}

		return $next($request);
	}
}
