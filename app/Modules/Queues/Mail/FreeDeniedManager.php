<?php

namespace App\Modules\Queues\Mail;

use App\Modules\Queues\Models\Queue;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FreeDeniedManager extends Mailable
{
	use Queueable, SerializesModels;

	/**
	 * The User
	 *
	 * @var User
	 */
	protected $user;

	/**
	 * Denied users
	 *
	 * @var array
	 */
	protected $denials;

	/**
	 * Create a new message instance.
	 *
	 * @return void
	 */
	public function __construct(User $user, $denials = array())
	{
		$this->user = $user;
		$this->denials = $denials;
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build()
	{
		return $this->markdown('queues::mail.freedenied.manager')
					->subject(trans('queues::mail.freedenied'))
					->with([
						'user' => $this->user,
						'denials' => $this->denials,
					]);
	}
}
