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

    // Inyectamos el servicio aquí automáticamente
    public function build(ComprobanteService $comprobanteService)
    {
        // 1. Pedimos al servicio que genere el PDF en modo 'content' (datos crudos)
        $pdfContent = $comprobanteService->generarPdf($this->venta, 'content');

        // 2. Definimos el nombre del archivo
        $nombreArchivo = "{$this->venta->ruc_emisor}-{$this->venta->tipo_comprobante}-{$this->venta->serie}-{$this->venta->numero}.pdf";

        return $this->subject('Tu Comprobante Electrónico')
            ->view('emails.comprobante') // La vista del cuerpo del correo (el HTML simple)
            ->attachData($pdfContent, $nombreArchivo, [
                'mime' => 'application/pdf',
            ]);
    }
}
