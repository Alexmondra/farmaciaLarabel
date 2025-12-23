<?php

namespace App\Http\Controllers\SunatVerificacion; // <--- Ojo con esto

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ventas\Venta;
use App\Services\Sunat\SunatService;

class FacturacionController extends Controller
{
    protected $sunatService;

    public function __construct(SunatService $sunatService)
    {
        $this->sunatService = $sunatService;
    }

    public function indexPendientes()
    {
        $ventas = Venta::with(['cliente', 'sucursal'])
            ->whereIn('tipo_comprobante', ['FACTURA', 'BOLETA'])
            ->where('estado', '!=', 'ACEPTADA')
            ->where('estado', '!=', 'ANULADO')
            ->orderBy('fecha_emision', 'desc')
            ->paginate(20);

        return view('rectificacionSunat.pendientes', compact('ventas'));
    }


    public function reenviar($id)
    {
        $venta = Venta::findOrFail($id);

        if ($venta->estado === 'ACEPTADA') {
            return back()->with('info', 'Este comprobante ya fue aceptado por SUNAT.');
        }

        $exito = $this->sunatService->transmitirAComprobante($venta);

        if ($exito) {
            return back()->with('success', '¡Envío exitoso! Comprobante aceptado.');
        } else {
            return back()->with('error', 'Falló el envío. Revisa el mensaje de error: ' . $venta->mensaje_sunat);
        }
    }


    public function verEstado($id) {}
}
