<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
// use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
// use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeWineShopMail extends Mailable
{
    use Queueable, SerializesModels;
    public string $userName;
    /**
     * Create a new message instance.
     */
    public function __construct(string $userName)
    {
        //
        $this->userName = $userName;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome Wine Shop Mail',
        );
    }
        /**
     * Build the message.
     */
    public function build()
    {
        return $this
            ->subject('Welcome To Wine Shop')
            ->view('emails.welcome-wine-shop');
    }

    /**
     * Get the message content definition.
     */
    // public function content(): Content
    // {
    //     return new Content(
    //         view: 'view.name',
    //     );
    // }

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
