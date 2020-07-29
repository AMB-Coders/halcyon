<?php

namespace App\Modules\Queues\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use App\Modules\Queues\Console\EmailQueueAuthorizedCommand;
use App\Modules\Queues\Console\EmailQueueDeniedCommand;
use App\Modules\Queues\Console\EmailQueueRemovedCommand;
use App\Modules\Queues\Console\EmailQueueRequestedCommand;
use App\Modules\Queues\Console\EmailWelcomeClusterCommand;
use App\Modules\Queues\Console\EmailWelcomeFreeCommand;

class ScheduleServiceProvider extends ServiceProvider
{
	public function boot()
	{
		$this->app->booted(function ()
		{
			$schedule = $this->app->make(Schedule::class);

			$schedule->command(EmailQueueAuthorizedCommand::class)->cron(config('module.queues.schedule.emailqueueauthorized', '*/10 * * * *'));

			$schedule->command(EmailQueueDeniedCommand::class)->cron(config('module.queues.schedule.emailqueuedenied', '*/20 * * * *'));

			$schedule->command(EmailQueueRemovedCommand::class)->cron(config('module.queues.schedule.emailqueueremoved', '*/10 * * * *'));

			$schedule->command(EmailQueueRequestedCommand::class)->cron(config('module.queues.schedule.emailqueuerequested', '*/20 * * * *'));

			$schedule->command(EmailWelcomeClusterCommand::class)->cron(config('module.queues.schedule.emailwelcomecluster', '0 5 * * * '));

			$schedule->command(EmailWelcomeFreeCommand::class)->cron(config('module.queues.schedule.emailwelcomefree', '0 5 * * * '));
			//$schedule->command(EmailQueueRemovedExpiredCommand::class)->cron(config('module.queues.schedule.emailqueueremovedexpired', '*/30 * * * *'));
		});
	}
}
