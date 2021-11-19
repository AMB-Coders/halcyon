<?php

namespace App\Modules\Courses\Console;

use App\Modules\Courses\Mail\Removed;
use App\Modules\Courses\Models\Account;
use App\Modules\Courses\Models\Member;
use App\Modules\Users\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class EmailRemovalsCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'courses:emailremovals {--debug : Output actions that would be taken without making them}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Email latest Contact Reports to subscribers.';

	/**
	 * Execute the console command.
	 * 
	 * @return  void
	 */
	public function handle()
	{
		$debug = $this->option('debug') ? true : false;

		$members = Member::query()
			->withTrashed()
			->where('notice', '=', 2)
			->orderBy('id', 'asc')
			->get();

		if (!count($members))
		{
			if ($debug || $this->output->isVerbose())
			{
				$this->comment('No new removals to email.');
			}
			return;
		}

		// Group activity by groupid so we can determine when to send the group mail
		$class_activity = array();
		foreach ($members as $user)
		{
			if (!isset($class_activity[$user->classaccountid]))
			{
				$class_activity[$user->classaccountid] = array();
			}

			array_push($class_activity[$user->classaccountid], $user);
		}

		$now = date("U");
		$threshold = 1200; // threshold for when considering activity "done"

		foreach ($class_activity as $courseid => $accounts)
		{
			// Find the latest activity
			$latest = 0;
			foreach ($accounts as $g)
			{
				if ($g->datetimecreated->timestamp > $latest)
				{
					$latest = $g->datetimecreated->timestamp;
				}
			}

			if ($now - $latest < $threshold)
			{
				continue;
			}

			$course = Account::find($courseid);

			if (!$course)
			{
				if ($debug || $this->output->isVerbose())
				{
					$this->error('Could not find course account #' . $courseid);
				}
				continue;
			}

			$user = User::find($course->userid);

			if (!$user)
			{
				if ($debug || $this->output->isVerbose())
				{
					$this->error('Could not find instructor for course account #' . $courseid);
				}
				continue;
			}

			// Prepare and send actual email
			$message = new Removed($user, $course, $accounts);

			if ($this->output->isDebug())
			{
				echo $message->render();
			}

			if ($debug || $this->output->isVerbose())
			{
				$this->info("Emailed course removals to {$user->email}.");
			}

			if ($debug)
			{
				continue;
			}

			if (!$user->email)
			{
				if ($debug || $this->output->isVerbose())
				{
					$this->error("Email address not found for user {$user->name}.");
				}
				continue;
			}

			Mail::to($user->email)->send($message);

			// Change states
			$course->update(['notice' => 0]);

			foreach ($accounts as $account)
			{
				$account->update(['notice' => 0]);
			}
		}
	}
}
