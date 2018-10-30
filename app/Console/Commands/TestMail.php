<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:send {to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send mail to recipient';

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
        $to = $this->argument('to');

        Mail::send('contact/contact', ['class' => 'user'], function ($m) use ($to) {
            $m->from(config('app.MAIL_FROM', 'no-reply@finite-soft.com'), config('app.APP_NAME'));
            $m->to($to, 'test user');
            $m->subject('Test mail');
        });

        $this->info('Mail sent');
    }
}
