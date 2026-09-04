<?php

namespace App\Mail;

use App\Models\TeamInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TeamInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public TeamInvitation $invitation, public string $token) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Undangan bergabung ke '.$this->invitation->team->name);
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.team-invitation');
    }

    public function attachments(): array
    {
        return [];
    }
}
