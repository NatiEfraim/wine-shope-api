<?php

namespace App\Mail;

use App\Enum\StatusEnum;
use App\Models\Booking;
use Illuminate\Bus\Queueable;
// use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
// use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingStatusUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;
    public Booking $booking;
    public string $statusHebrew;
    /**
     * Create a new message instance.
     */
    public function __construct(Booking $booking)
    {
        //
        $this->booking = $booking;
        $this->statusHebrew = StatusEnum::hebrew($booking->status_id);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Booking Status Updated Mail');
    }
    
    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('עדכון סטטוס להזמנה שלך')->view('emails.booking-status-updated');
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
