<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Facades\URL;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class VerifyCodeAdmin extends Mailable
{
    use Queueable, SerializesModels;

    protected $code;

    /**
     * Create a new message instance.
     */
    public function __construct(protected User $user)
    {
        $this->user = $user;
        $this->code = strval(rand(1000, 9999));
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Verify Account',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $this->user->code =  Hash::make($this->code);
        $this->user->save();
        return new Content(
            view: 'emailcode',
            with: [
                'code' => $this->code,
                'name' => $this->user->name,
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
