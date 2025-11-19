<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class TestEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:email';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email configuration with Mailpit';

    /**
     * Execute the console command.
     */
    public function handle()
    {
            $user = User::first();
        
        if (!$user) {
            $this->error('No users found in database. Run migrations and seeders first.');
            return;
        }

        try {
            Mail::raw('Hello, this is a test email!', function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('Test Email from Ticket System');
            });

            $this->info('Test email sent successfully!');
            $this->info('Check Mailpit');
            
        } catch (\Exception $e) {
            $this->error('Failed to send email: ' . $e->getMessage());
        }
    }
}
