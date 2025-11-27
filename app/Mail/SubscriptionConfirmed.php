<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class SubscriptionConfirmed extends Mailable
{
    public $email;

    public function __construct($email)
    {
        $this->email = $email;
    }

    public function build()
    {
        return $this->subject("You're subscribed!")
                    ->view('emails.subscription-confirmed');
    }
}
