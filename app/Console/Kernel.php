<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        if (config('app.IS_TOOL')) {
            $schedule->command('tool:sendpending')->everyMinute();
        } else {
            $schedule->command('newsletter:send post')->everyFiveMinutes();
            $schedule->command('newsletter:send daily')->dailyAt(config('app.NEWSLETTER_SEND_TIME'));
            $schedule->command('newsletter:send weekly')->weeklyOn(1, config('app.NEWSLETTER_SEND_TIME'));
            $schedule->command('newsletter:send monthly')->monthlyOn(1, config('app.NEWSLETTER_SEND_TIME'));
            $schedule->command('resource:sendpending')->everyMinute();
            $schedule->command('update:counters')->everyTenMinutes();
        }
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ .'/Commands');

        require base_path('routes/console.php');
    }
}
