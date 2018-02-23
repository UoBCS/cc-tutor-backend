<?php

namespace App\Infrastructure\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ClassInvitationEmail extends Mailable
{
    use Queueable, SerializesModels;

    protected $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('email.class_invitation')
                    ->subject('CC Tutor: class invitation')
                    ->with(['token' => $this->token]);
    }
}
