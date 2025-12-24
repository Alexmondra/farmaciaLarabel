<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ComprobanteMailable extends Mailable
{
    use Queueable, SerializesModels;

    public $venta;

    public function __construct($venta)
    {
        $this->venta = $venta;
    }

    public function build()
    {
        $pdf = \PDF::loadView('reportes.pdf_venta', ['venta' => $this->venta]); // Usa tu vista de PDF actual

        return $this->subject('Tu Comprobante Electrónico - ' . config('app.name'))
            ->view('emails.comprobante') // Vista simple del cuerpo del correo
            ->attachData($pdf->output(), "Comprobante-{$this->venta->serie}-{$this->venta->numero}.pdf", [
                'mime' => 'application/pdf',
            ]);
    }
}
