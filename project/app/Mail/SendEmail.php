<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $nama, $email, $pesan;

    public function __construct($nama, $email, $pesan)
    {
        $this->nama   = $nama;
        $this->email  = $email;
        $this->pesan  = $pesan;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('noreply@enerzy.id')->to('contactus@enerzy.id')->view('email/contactus')->with([
            'nama' => $this->nama,
            'email' => $this->email,
            'pesan' => $this->pesan,
        ]);
    }
}
