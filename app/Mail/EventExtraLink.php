<?php

namespace App\Mail;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EventExtraLink extends Mailable
{
    use Queueable, SerializesModels;

    public $event;

    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    public function build()
    {
        return $this->subject('Link Akses Event: ' . $this->event->title)
            ->view('emails.extra-link')
            ->with([
                'event' => $this->event
            ]);
    }

}
