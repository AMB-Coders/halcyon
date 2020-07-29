<?php

namespace App\Modules\Queues\Events;

use App\Modules\Queues\Models\UserRequest;

class UserRequestUpdated
{
	/**
	 * @var User
	 */
	public $userrequest;

	/**
	 * Constructor
	 *
	 * @param User $user
	 * @param array $data
	 * @return void
	 */
	public function __construct(UserRequest $userrequest)
	{
		$this->userrequest = $userrequest
	}
}
