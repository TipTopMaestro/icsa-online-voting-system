<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CandidateCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $candidateName;
    public string $email;
    public string $password;
    public string $electionTitle;
    public string $positionName;
    public string $loginUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(
        string $candidateName,
        string $email,
        string $password,
        string $electionTitle,
        string $positionName
    ) {
        $this->candidateName = $candidateName;
        $this->email = $email;
        $this->password = $password;
        $this->electionTitle = $electionTitle;
        $this->positionName = $positionName;
        $this->loginUrl = config('app.url') . '/login';
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Candidate Account Credentials',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.candidate-credentials',
            with: [
                'candidateName' => $this->candidateName,
                'email' => $this->email,
                'password' => $this->password,
                'electionTitle' => $this->electionTitle,
                'positionName' => $this->positionName,
                'loginUrl' => $this->loginUrl,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
