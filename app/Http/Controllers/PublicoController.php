<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ventas\Venta; // Tu modelo
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\URL;

class PublicoController extends Controller
{
    // 1. Procesar la búsqueda del cliente
    public function buscar(Request $request)
    {
        $request->validate([
            'tipo' => 'required',
            'serie' => 'required',
            'numero' => 'required|numeric',
            'total' => 'required|numeric',
            'fecha' => 'required|date'
        ]);

        // Buscamos coincidencia EXACTA
        $venta = Venta::where('tipo_comprobante', $request->tipo)
            ->where('serie', $request->serie)
            ->where('numero', $request->numero)
            ->where('total_neto', $request->total) // O total_bruto, según lo que imprimas
            ->whereDate('fecha_emision', $request->fecha)
            ->first();

        if (!$venta) {
            return back()->with('error', 'No se encontró ningún comprobante con esos datos. Verifique su ticket.');
        }

        // Si existe, generamos un LINK SEGURO (Signed URL) temporal
        // Esto evita que tengamos que exponer el ID real en la vista
        $urlDescarga = URL::signedRoute('publico.descargar', ['id' => $venta->id]);

        return back()->with('exito', true)->with('url_descarga', $urlDescarga);
    }

    // 2. Generar el PDF (Solo si el link es válido)
    public function descargar(Request $request, $id)
    {
        // El middleware 'signed' ya verificó que el link es seguro
        $venta = Venta::findOrFail($id);

        // Cargar vista PDF
        $pdf = PDF::loadView('comprobante_pdf', compact('venta'));
        return $pdf->stream('comprobante.pdf');
    }
}
