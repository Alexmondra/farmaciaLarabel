<?php

namespace App\Services;

use App\Models\Ventas\Venta;
use App\Models\Configuracion;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Luecano\NumeroALetras\NumeroALetras;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\Guias\GuiaRemision;

class ComprobanteService
{
    public function generarPdf(Venta $venta, $tipo = 'stream')
    {
        $config = Configuracion::firstOrFail();

        // 1. Datos del Emisor
        $datosEmisor = [
            'nombre'    => $venta->sucursal->nombre,
            'direccion' => $venta->sucursal->direccion . " - " . $venta->sucursal->distrito,
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

        // 2. Generar QR (UNIFICADO)
        $qrParams = [
            'ruc'              => $datosEmisor['ruc'],
            'tipo_doc'         => $tipoDoc,
            'serie'            => $venta->serie,
            'numero'           => $venta->numero,
            'hash'             => $venta->hash ?? '',
            'monto_igv'        => $venta->total_igv,
            'monto_neto'       => $venta->total_neto,
            'fecha'            => $fecha,
            'tipo_doc_cliente' => $clienteDocType,
            'doc_cliente'      => $venta->cliente->documento,
        ];
        $qrBase64 = $this->generarQrBase64($qrParams);

        $montoLetras = $this->convertirTotalALetras($venta->total_neto);

        $data = [
            'venta'       => $venta,
            'emisor'      => $datosEmisor,
            'qrBase64'    => $qrBase64,
            'logoBase64'  => $logoBase64,
            'montoLetras' => $montoLetras
        ];
        $nombreArchivo = "{$datosEmisor['ruc']}-{$tipoDoc}-{$venta->serie}-{$venta->numero}.pdf";

        return $this->renderizarPdf('comprobante_pdf', $data, $nombreArchivo, $tipo);
    }

    public function generarGuiaPdf(GuiaRemision $guia, $tipo = 'stream')
    {
        $config = Configuracion::firstOrFail();

        // 1. Datos del Emisor
        $datosEmisor = [
            'nombre'       => $guia->sucursal->nombre,
            'direccion'    => $guia->sucursal->direccion,
            'ruc'          => $config->empresa_ruc,
            'razon_social' => $config->empresa_razon_social
        ];

        $rutaLogo = $config->ruta_logo;
        $logoBase64 = $this->obtenerImagenBase64($rutaLogo);

        $tipoDoc = '09';

        $qrParams = [
            'ruc'              => $datosEmisor['ruc'],
            'tipo_doc'         => $tipoDoc,
            'serie'            => $guia->serie,
            'numero'           => $guia->numero,
            'hash'             => $guia->hash ?? '',
        ];
        $qrBase64 = $this->generarQrBase64($qrParams);

        // 3. Datos y Nombre de Archivo
        $data = [
            'guia' => $guia,
            'emisor' => $datosEmisor,
            'logoBase64' => $logoBase64,
            'qrBase64' => $qrBase64,
            'fecha_impresion' => now()->format('d/m/Y H:i:s'),
        ];
        $nombreArchivo = "{$datosEmisor['ruc']}-{$tipoDoc}-{$guia->serie}-{$guia->numero}.pdf";

        return $this->renderizarPdf('guias.show', $data, $nombreArchivo, $tipo);
    }


    private function renderizarPdf(string $view, array $data, string $nombreArchivo, string $tipo)
    {
        $pdf = Pdf::loadView($view, $data);
        $pdf->setPaper('A4', 'portrait');

        if ($tipo === 'download') {
            return $pdf->download($nombreArchivo);
        }

        if ($tipo === 'content') {
            return $pdf->output();
        }
        return $pdf->stream($nombreArchivo);
    }

    private function generarQrBase64(array $params): string
    {
        $qrString = "";

        if ($params['tipo_doc'] === '09') {
            $qrString = "{$params['ruc']}|{$params['tipo_doc']}|{$params['serie']}|{$params['numero']}|{$params['hash']}|";
        } else {
            $qrString = "{$params['ruc']}|{$params['tipo_doc']}|{$params['serie']}|{$params['numero']}|{$params['monto_igv']}|{$params['monto_neto']}|{$params['fecha']}|{$params['tipo_doc_cliente']}|{$params['doc_cliente']}|{$params['hash']}|";
        }

        return base64_encode(QrCode::format('svg')->size(150)->generate($qrString));
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
            return null;
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
