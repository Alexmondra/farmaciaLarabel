<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ventas\Venta;
use Illuminate\Support\Facades\URL;
use App\Services\ComprobanteService;

class PublicoController extends Controller
{
    public function buscar(Request $request)
    {
        $request->validate([
            'tipo'   => 'required',
            'serie'  => 'required',
            'numero' => 'required', // Quitamos 'numeric' estricto para limpiarlo nosotros
            'total'  => 'required|numeric',
            'fecha'  => 'required|date'
        ]);

        // LOGICA INTELIGENTE:
        // 1. Convertimos el numero a entero (00008 pasa a ser 8)
        $numeroLimpio = (int) $request->numero;

        // 2. Buscamos
        $venta = Venta::where('tipo_comprobante', $request->tipo)
            ->where('serie', strtoupper($request->serie)) // Forzamos mayúsculas
            ->where('numero', $numeroLimpio)              // Usamos el número limpio
            ->where('total_neto', $request->total)        // Aceptará 32.4 o 32.40
            ->whereDate('fecha_emision', $request->fecha)
            ->first();

        if (!$venta) {
            return back()->with('error', 'No se encontró el comprobante. Verifique la fecha y el monto exacto.');
        }

        // 3. Generamos Link Seguro
        $urlDescarga = URL::temporarySignedRoute(
            'publico.descargar',
            now()->addMinutes(30),
            ['id' => $venta->id]
        );

        return back()->with('exito', true)->with('url_descarga', $urlDescarga);
    }

    public function descargar(Request $request, $id, ComprobanteService $pdfService)
    {
        $venta = Venta::with(['detalles.medicamento', 'cliente', 'sucursal'])->findOrFail($id);
        // 'stream' permite que el navegador lo muestre en lugar de bajarlo directo
        return $pdfService->generarPdf($venta, 'stream');
    }
}
