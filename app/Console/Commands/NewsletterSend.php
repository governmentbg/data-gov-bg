<?php

namespace App\Console\Commands;

use App\User;
use App\Page;
use App\Module;
use App\ActionsHistory;
use Carbon\Carbon;
use App\UserSetting;
use Illuminate\Http\Request;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Api\ActionsHistoryController as ApiActionsHistory;

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
            case 'post':
                $postNewsletter = $this->getPeriodicNewsletters(UserSetting::DIGEST_FREQ_ON_POST);
                $from = Carbon::now()->subMinutes(5);

                if (count($postNewsletter)) {
                    $this->sendPeriodicNewsletters($postNewsletter, $from, Carbon::now());
                }

                break;
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
            $roles = User::getUserRoles($user->id);
            session()->put('roles', $roles);
            $rq = Request::create('/user/newsFeed?from='. $from .'&to='. $to .'&perPage=5000', 'GET', []);
            $userController = new UserController($rq);
            $newsResult = $userController->newsFeed($rq)->getData();
            Auth::logout();

            if (!empty($newsResult['actionsHistory'])) {
                $mailData = is_array($newsResult)
                    ? array_merge($params, $newsResult)
                    : $params;

                Mail::send('mail/newsletter', $mailData, function ($m) use ($mailData) {
                    $m->from(config('app.MAIL_FROM'), config('app.APP_NAME'));
                    $m->to($mailData['mail'], $mailData['user']);
                    $m->subject(__('custom.newsletter'));
                });
            }

            $criteria = [];
            $criteria['period_from'] = $from;
            $criteria['period_to'] = $to;
            $criteria['module'] = Module::getModules()[Module::NEWS];
            $criteria['actions'] = [ActionsHistory::TYPE_ADD, ActionsHistory::TYPE_MOD];
            $qrParams = [
                'api_key'          => $user->api_key,
                'criteria'         => $criteria,
                'records_per_page' => 5000,
                'page_number'      => 1,
            ];

            $rq = Request::create('/api/listActionHistory', 'POST', $qrParams);
            $api = new ApiActionsHistory($rq);
            $result = $api->listActionHistory($rq)->getData();
            $result->actions_history = isset($result->actions_history) ? $result->actions_history : [];

            if (!empty($result->actions_history)) {
                $mailData = array_merge($params, ['actions' => $result->actions_history]);

                foreach ($mailData['actions'] as $key => $action) {
                    $actObject = Page::where('id', $action->action_object)->first();
                    $occurrence = Carbon::createFromFormat('Y-m-d H:i:s', $action->occurrence);
                    $now = Carbon::parse(Carbon::now());
                    $timeDiff = $occurrence->diffForHumans($now);
                    $mailData['actions'][$key]->user_profile = url('/user/profile/'. $action->user_id);
                    $mailData['actions'][$key]->object = $actObject->title;
                    $mailData['actions'][$key]->url = url('/news/view/'. $action->action_object);
                    $mailData['actions'][$key]->time = $timeDiff;
                    $mailData['actions'][$key]->text = $action->action == ActionsHistory::TYPE_ADD
                        ? __('custom.add_news')
                        : __('custom.edit_news');
                }

                Mail::send('mail/news', $mailData, function ($m) use ($mailData) {
                    $m->from(config('app.MAIL_FROM'), config('app.APP_NAME'));
                    $m->to($mailData['mail'], $mailData['user']);
                    $m->subject(__('custom.newsletter'));
                });
            }
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
