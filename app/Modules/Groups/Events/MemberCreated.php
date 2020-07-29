<?php

namespace App\Modules\Groups\Events;

use App\Modules\Groups\Models\Member;

class MemberCreated
{
	/**
	 * @var Member
	 */
	public $member;

	/**
	 * Constructor
	 *
	 * @param Member $member
	 * @return void
	 */
	public function __construct(Member $member)
	{
		$this->member = $member;
	}
}
