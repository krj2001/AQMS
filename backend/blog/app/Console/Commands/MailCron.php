<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Illuminate\Support\Facades\Mail;
use App\Mail\MailNotify;

class MailCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Mail:Cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     * @return int
     */
    public function handle()
    {
        //return 0;
        $email = "abhishek@rdltech.in";
        $data = [
            'userid'=>$email,
            'subject' => 'Application employee Credentials',
            'body' =>"from Cron time"
        ];

        Mail::send('credentialmail',$data, function($messages) use ($email){
            $messages->to($email);
            $messages->subject('Application login credentials');        
        });
    }
}
