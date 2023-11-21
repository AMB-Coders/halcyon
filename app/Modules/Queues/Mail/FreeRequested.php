<?php

namespace App\Modules\Queues\Mail;

use App\Modules\Queues\Mail\Traits\HeadersAndTags;
use App\Modules\Users\Models\User;
use App\Modules\Queues\Models\UserRequest;
use App\Modules\Queues\Models\User as QueueUser;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FreeRequested extends Mailable
{
	use Queueable, SerializesModels, HeadersAndTags;

	/**
	 * The User
	 *
	 * @var User
	 */
	protected $user;

	/**
	 * The user request
	 *
	 * @var array<int,array<int,QueueUser>>
	 */
	protected $userrequests;

	/**
	 * Create a new message instance.
	 *
	 * @param User $user
	 * @param array<int,array<int,QueueUser>> $userrequests
	 * @return void
	 */
	public function __construct(User $user, $userrequests)
	{
		$this->user = $user;
		$this->userrequests = $userrequests;

		$this->mailTags[] = 'queue-requested';
		$this->mailTags[] = 'queue-free';
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build()
	{
		return $this->markdown('queues::mail.freerequested')
					->subject(trans('queues::mail.freerequested'))
					->with([
						'user' => $this->user,
						'requests' => $this->userrequests
					]);
	}
}
