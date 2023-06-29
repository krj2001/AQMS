<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\commands\MailCron;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */

    protected $command = [
        Commands\MailCron::class
    ];

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('mail:cron')->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}


#2022-05-30T15:59:44+05:30] Running scheduled command: "D:\xampp\php\php.exe" "artisan" demo:cron > "NUL" 2>&1