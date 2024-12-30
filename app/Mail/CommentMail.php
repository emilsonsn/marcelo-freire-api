<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CommentMail extends Mailable
{
    use Queueable, SerializesModels;

    public $clientName;
    public $comment;

    /**
     * Create a new message instance.
     */
    public function __construct($clientName, $comment)
    {
        $this->clientName = $clientName;
        $this->comment = $comment;
    }

    /**
     * Get the message envelope.
     */
    
    public function build()
    {
        return $this->view('emails.comment')
                    ->with([
                        'clientName' => $this->clientName,
                        'comment' => $this->comment,
                    ])
                    ->subject('Comentário adicionado');
    }
}
