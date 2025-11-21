<?php

namespace App\Mail;

use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public Notification $notification;

    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->notification->title,
        );
    }

    public function build()
    {
        return $this->view('emails.notification')
                    ->with([
                        'notification' => $this->notification,
                    ]);
    }

    public function attachments(): array
    {
        return [];
    }
}
