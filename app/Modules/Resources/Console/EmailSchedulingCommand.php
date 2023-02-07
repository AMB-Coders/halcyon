<?php

namespace App\Modules\Resources\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Modules\Resources\Models\Subresource;
use App\Modules\Resources\Models\Child;
use App\Modules\Resources\Models\Asset;
use App\Modules\Resources\Mail\Scheduling;

class EmailSchedulingCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $signature = 'resources:emailscheduling {--debug : Output emails rather than sending}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Email started/stopped status of scheduling on subresources.';

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function handle(): void
	{
		$debug = $this->option('debug') ? true : false;
		$email = config('module.resources.admin_email');

		$a = (new Asset)->getTable();
		$s = (new Subresource)->getTable();
		$c = (new Child)->getTable();

		$stopped = Subresource::query()
			->select($s . '.*')
			->join($c, $c . '.subresourceid', $s . '.id')
			->join($a, $a . '.id', $c . '.resourceid')
			->whereNull($a . '.datetimeremoved')
			->where($s . '.notice', '=', 2)
			->get();

		if (count($stopped))
		{
			$message = new Scheduling('stopped', array(), $stopped);

			if ($this->output->isDebug())
			{
				echo $message->render();
			}

			if ($debug)
			{
				$this->info("Emailed stopped scheduling to {$email}.");
			}
			else
			{
				Mail::to($email)->send($message);

				if ($this->output->isVerbose())
				{
					$this->info("Emailed stopped scheduling to {$email}.");
				}

				$this->log($email, "Emailed stopped scheduling.");

				foreach ($stopped as $subresource)
				{
					$subresource->update(['notice' => 3]);
				}
			}
		}
		elseif ($debug || $this->output->isVerbose())
		{
			$this->info('No stopped queues found.');
		}

		$started = Subresource::query()
			->select($s . '.*')
			->join($c, $c . '.subresourceid', $s . '.id')
			->join($a, $a . '.id', $c . '.resourceid')
			->whereNull($a . '.datetimeremoved')
			->where($s . '.notice', '=', 1)
			->get();

		$stopped = Subresource::query()
			->select($s . '.*')
			->join($c, $c . '.subresourceid', $s . '.id')
			->join($a, $a . '.id', $c . '.resourceid')
			->whereNull($a . '.datetimeremoved')
			->where($s . '.notice', '=', 3)
			->get();

		if (count($started))
		{
			$message = new Scheduling('started', $started, $stopped);

			if ($this->output->isDebug())
			{
				echo $message->render();
			}

			if ($debug || $this->output->isVerbose())
			{
				$this->info("Emailed started scheduling to {$email}.");

				if ($debug)
				{
					return;
				}
			}

			Mail::to($email)->send($message);

			$this->log($email, 'Emailed started scheduling.');

			foreach ($started as $subresource)
			{
				$subresource->update(['notice' => 0]);
			}
		}
		elseif ($debug || $this->output->isVerbose())
		{
			$this->info('No newly started queues found.');
		}
	}

	/**
	 * Log email
	 *
	 * @param   string  $uri
	 * @param   mixed   $payload
	 * @return  void
	 */
	protected function log($uri = '', $payload = ''): void
	{
		Log::create([
			'ip'              => request()->ip(),
			'userid'          => (auth()->user() ? auth()->user()->id : 0),
			'status'          => 200,
			'transportmethod' => 'POST',
			'servername'      => request()->getHttpHost(),
			'uri'             => $uri,
			'app'             => 'email',
			'payload'         => $payload,
			'classname'       => 'resources:emailscheduling',
			'classmethod'     => 'handle',
		]);
	}
}
