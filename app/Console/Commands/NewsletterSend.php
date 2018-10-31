<?php

namespace App\Console\Commands;

use App\User;
use Carbon\Carbon;
use App\UserSetting;
use Illuminate\Http\Request;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\UserController;

class NewsletterSend extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'newsletter:send {type}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send newsletters';
    protected $to = null;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->to = Carbon::today();

        switch ($this->argument('type')) {
            case 'daily':
                $dailyNewsletters = $this->getPeriodicNewsletters(UserSetting::DIGEST_FREQ_ONCE_DAY);
                $from = Carbon::today()->subDays(1);

                if (count($dailyNewsletters)) {
                    $this->sendPeriodicNewsletters($dailyNewsletters, $from, $this->to);
                }

                break;
            case 'weekly':
                $weeklyNewsletters = $this->getPeriodicNewsletters(UserSetting::DIGEST_FREQ_ONCE_WEEK);
                $from = Carbon::today()->subWeeks(1);

                if (count($weeklyNewsletters)) {
                    $this->sendPeriodicNewsletters($weeklyNewsletters, $from, $this->to);
                }

                break;
            case 'monthly':
                $monthlyNewsletters = $this->getPeriodicNewsletters(UserSetting::DIGEST_FREQ_ONCE_MONTH);
                $from = Carbon::today()->subMonths(1);

                if (count($monthlyNewsletters)) {
                    $this->sendPeriodicNewsletters($monthlyNewsletters, $from, $this->to);
                }

                break;
            default:
                break;
        }
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
                $m->from(config('app.MAIL_FROM'), config('app.APP_NAME'));
                $m->to($mailData['mail'], $mailData['user']);
                $m->subject(__('custom.newsletter'));
            });
        }

        return true;
    }

    public function getPeriodicNewsletters($digest)
    {
        $newsletters = UserSetting::where('newsletter_digest', $digest)
            ->with('user')
            ->get();

        return $newsletters;
    }
}
