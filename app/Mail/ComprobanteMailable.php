<?php

namespace App\Mail;

use App\Models\Ventas\Venta;
use App\Services\ComprobanteService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ComprobanteMailable extends Mailable
{
    use Queueable, SerializesModels;

    public $venta;

    public function __construct(Venta $venta)
    {
        $this->venta = $venta;
    }

    public function build(ComprobanteService $comprobanteService)
    {
        $pdfContent = $comprobanteService->generarPdf($this->venta, 'content');

        $nombreArchivo = "{$this->venta->ruc_emisor}-{$this->venta->tipo_comprobante}-{$this->venta->serie}-{$this->venta->numero}.pdf";

        return $this->subject('Tu Comprobante Electrónico')
            ->view('emails.comprobante')
            ->attachData($pdfContent, $nombreArchivo, [
                'mime' => 'application/pdf',
            ]);
    }
}
