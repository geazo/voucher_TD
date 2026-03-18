<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class VoucherMail extends Mailable
{
    public $penerima;
    public $voucher;
    public $pdfUrl;

    public function __construct($penerima, $voucher, $pdfUrl)
    {
        $this->penerima = $penerima;
        $this->voucher = $voucher;
        $this->pdfUrl = $pdfUrl;
    }

    public function build()
    {
        return $this->view('emails.voucher')
            ->subject('Your Voucher')
            ->with([
                'penerima' => $this->penerima,
                'voucher' => $this->voucher,
                'pdfUrl' => $this->pdfUrl,
            ]);
    }
}
