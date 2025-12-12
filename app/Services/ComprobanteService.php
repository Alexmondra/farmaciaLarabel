<?php

namespace App\Services;

use App\Models\Ventas\Venta;
use App\Models\Configuracion;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Luecano\NumeroALetras\NumeroALetras;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ComprobanteService
{
    public function generarPdf(Venta $venta, $tipo = 'stream')
    {
        $config = Configuracion::firstOrFail();

        $datosEmisor = [
            'nombre'    => $venta->sucursal->nombre,
            'direccion' => $venta->sucursal->direccion . " - " . $venta->sucursal->distrito, // Dirección Real
            'telefono'  => $venta->sucursal->telefono,
            'email'     => $venta->sucursal->email,
            'ruc'          => $config->empresa_ruc,
            'razon_social' => $config->empresa_razon_social
        ];

        $rutaLogo = $config->ruta_logo ?? $venta->sucursal->imagen_sucursal;
        $logoBase64 = $this->obtenerImagenBase64($rutaLogo);

        $tipoDoc = $venta->tipo_comprobante == 'FACTURA' ? '01' : '03';
        $fecha   = $venta->fecha_emision->format('Y-m-d');
        $clienteDocType = strlen($venta->cliente->documento) == 11 ? '6' : '1';
        $hash    = $venta->hash ?? '';

        $qrString = "{$datosEmisor['ruc']}|{$tipoDoc}|{$venta->serie}|{$venta->numero}|{$venta->total_igv}|{$venta->total_neto}|{$fecha}|{$clienteDocType}|{$venta->cliente->documento}|{$hash}|";

        $qrBase64 = base64_encode(QrCode::format('svg')->size(150)->generate($qrString));

        // 5. CONVERTIR TOTAL A LETRAS
        $montoLetras = $this->convertirTotalALetras($venta->total_neto);

        // 6. RENDERIZAR
        $pdf = Pdf::loadView('comprobante_pdf', [
            'venta'       => $venta,
            'emisor'      => $datosEmisor, // Pasamos el array limpio
            'qrBase64'    => $qrBase64,
            'logoBase64'  => $logoBase64,
            'montoLetras' => $montoLetras
        ]);

        $pdf->setPaper('A4', 'portrait');

        // Nombre amigable: RUC-TIPO-SERIE-NUMERO.pdf
        $nombreArchivo = "{$datosEmisor['ruc']}-{$tipoDoc}-{$venta->serie}-{$venta->numero}.pdf";

        if ($tipo === 'download') {
            return $pdf->download($nombreArchivo);
        }
        return $pdf->stream($nombreArchivo);
    }

    private function obtenerImagenBase64($rutaRelativa)
    {
        $path = null;

        if ($rutaRelativa) {
            if (file_exists(storage_path('app/public/' . $rutaRelativa))) {
                $path = storage_path('app/public/' . $rutaRelativa);
            } elseif (file_exists(public_path($rutaRelativa))) {
                $path = public_path($rutaRelativa);
            }
        }

        if (!$path) {
            return null; // Si no hay logo, no devuelve nada (para no romper el PDF)
        }

        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        return 'data:image/' . $type . ';base64,' . base64_encode($data);
    }

    private function convertirTotalALetras($total)
    {
        $total = number_format($total, 2, '.', '');
        $split = explode('.', $total);
        $entero = $split[0];
        $decimal = $split[1];

        $formatter = new NumeroALetras();
        $letras = $formatter->toWords($entero);
        return "SON: " . Str::upper($letras) . " CON {$decimal}/100 SOLES";
    }
}
