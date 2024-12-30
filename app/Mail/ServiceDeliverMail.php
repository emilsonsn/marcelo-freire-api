<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ServiceDeliverMail extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $code;

    /**
     * Create a new message instance.
     */
    public function __construct($name, $code)
    {
        $this->name = $name;
        $this->code = $code;
    }

    /**
     * Get the message envelope.
     */
    
    public function build()
    {
        return $this->view('emails.service_deliver')
                    ->with([
                        'name' => $this->name,
                        'code' => $this->code,
                    ])
                    ->subject('Serviço entregue');
    }
}
