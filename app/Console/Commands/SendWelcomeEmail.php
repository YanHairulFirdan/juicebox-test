<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SendWelcomeEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:welcome-email emails={emails*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send welcome email to new users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $emails = explode(',', $this->argument('emails')[0]);

        User::query()
            ->whereIn('email', $emails)
            ->lazy()
            ->each(function ($user) {
                $user->notify(new \App\Notifications\WelcomeUser());
                $this->info("Welcome email sent to {$user->email}");
            });

        return 0;
    }
}
