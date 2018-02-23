<?php

namespace App\Infrastructure\Jobs;

use App\Api\Users\Models\User;
use App\Infrastructure\Mail\ClassInvitationEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Mail;

class SendClassInvitationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $emails;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, array $emails)
    {
        $this->user = $user;
        $this->emails = $emails;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $email = new ClassInvitationEmail($this->user->class_invitation_token);
        Mail::to($emails)->send($email);
    }
}
