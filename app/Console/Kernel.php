<?php

namespace App\Console;

use App\User;
use Carbon\Carbon;
use App\UserSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\UserController;
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
    protected $to = null;

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $this->to = Carbon::today();
        /*$schedule->call(function () {
            $dailyNewsletters = $this->getPeriodicNewsletters(UserSetting::DIGEST_FREQ_ONCE_DAY);
            $from = Carbon::today()->subMonths(1);
            if (false) {
                if (count($dailyNewsletters)) {
                    $this->sendPeriodicNewsletters($dailyNewsletters, $from, $this->to);
                }
            }
        })->everyMinute();*/

        $schedule->call(function () {
            $dailyNewsletters = $this->getPeriodicNewsletters(UserSetting::DIGEST_FREQ_ONCE_DAY);
            $from = Carbon::today()->subDays(1);

            if (count($dailyNewsletters)) {
                $this->sendPeriodicNewsletters($dailyNewsletters, $from, $this->to);
            }
        })->dailyAt('10:00');

        $schedule->call(function () {
            $weeklyNewsletters = $this->getPeriodicNewsletters(UserSetting::DIGEST_FREQ_ONCE_WEEK);
            $from = Carbon::today()->subWeeks(1);

            if (count($weeklyNewsletters)) {
                $this->sendPeriodicNewsletters($weeklyNewsletters, $from, $this->to);
            }
        })->weeklyOn(1, '10:00');

        $schedule->call(function () {
            $monthlyNewsletters = $this->getPeriodicNewsletters(UserSetting::DIGEST_FREQ_ONCE_MONTH);
            $from = Carbon::today()->subMonths(1);

            if (count($monthlyNewsletters)) {
                $this->sendPeriodicNewsletters($monthlyNewsletters, $from, $this->to);
            }
        })->monthlyOn(1, '10:00');
    }

    public function sendPeriodicNewsletters($newsletters, $from, $to)
    {
        foreach ($newsletters as $news) {
            $user = $news->user;
            $params = [
                'user'  => $user->firstname,
                'mail'  => $user->email,
            ];

            Auth::loginUsingId($user->id);
            $rq = Request::create('/user/newsFeed?from='. $from .'&to='. $to .'&perPage=5000', 'GET', []);
            $userController = new UserController($rq);
            $newsResult = $userController->newsFeed($rq)->getData();
            Auth::logout();

            $mailData = is_array($newsResult)
                ? array_merge($params, $newsResult)
                : $params;

            Mail::send('mail/newsletter', $mailData, function ($m) use ($mailData) {
                $m->from(env('MAIL_FROM', 'no-reply@finite-soft.com'), env('APP_NAME'));
                $m->to($mailData['mail'], $mailData['user']);
                $m->subject(__('custom.newsletter'));
            });
        }

        return true;
    }

    protected function getPeriodicNewsletters($digest)
    {
        $newsletters = UserSetting::where('newsletter_digest', $digest)
            ->with('user')
            ->get();

        return $newsletters;
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
