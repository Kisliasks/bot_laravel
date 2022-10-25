<?php

namespace App\Console;

use App\Helpers\Bothelper;
use App\Http\Controllers\StartWorkDayController;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Http\Controllers\BirthdayController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Http;


class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {    // отправка сообщений о предстоящем др
            $birth = new BirthdayController;
            $birth->birthday();

            })->timezone('Europe/Moscow')->dailyAt('10:00');

            $schedule->call(function () {    // таймаут сообщений о предстоящем др
                $birth = new BirthdayController;
                $birth->birthdayTimeOut();
    
                })->timezone('Europe/Moscow')->dailyAt('10:00');

        $schedule->call(function () {
            $work_day = new StartWorkDayController;
            $work_day->startWorkTimeOut();
                // время ответа работника истекло
        })->timezone('Europe/Moscow')->weekdays()->dailyAt('12:14');

        $schedule->call(function () {
            $work_day = new StartWorkDayController;
            $work_day->unsetWorkStatus();
                // обнуляем work status в конце дня
        })->timezone('Europe/Moscow')->weekdays()->dailyAt('15:05');

        $schedule->call(
              new StartWorkDayController
            // вывод статистики
     
        )->timezone('Europe/Moscow')->weekdays()->dailyAt('12:14');

        $schedule->call(function () {
            $work_day = new StartWorkDayController;
            $work_day->buttons();
     // предложение поработать
        })->timezone('Europe/Moscow')->weekdays()->dailyAt('12:12');
        
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
