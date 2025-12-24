<?php

namespace App\Http\Controllers\SunatVerificacion; // <--- Ojo con esto

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ventas\Cliente;
use App\Models\Ventas\Venta;
use App\Services\Sunat\SunatService;
use Illuminate\Support\Facades\DB;

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


    // En ClienteController.php

    public function buscarClienteVenta(Request $request)
    {
        $term = $request->input('q');

        if (!$term) {
            return response()->json([]);
        }

        $clientes = Cliente::query()
            ->where('documento', 'like', "%$term%")
            ->orWhere('razon_social', 'like', "%$term%")
            ->orWhereRaw("CONCAT(nombre, ' ', apellidos) LIKE ?", ["%$term%"])
            ->limit(10)
            ->get();

        $resultado = $clientes->map(function ($c) {
            return [
                'id' => $c->id,
                'text' => $c->documento . ' - ' . $c->nombre_completo
            ];
        });

        return response()->json($resultado);
    }

    public function edit($id)
    {
        $venta = Venta::with(['cliente', 'detalles.medicamento'])->findOrFail($id);

        if ($venta->estado === 'ACEPTADA') {
            return redirect()->route('facturacion.pendientes')
                ->with('error', 'No se puede editar un comprobante que ya fue aceptado.');
        }

        $clientes = Cliente::all();

        return view('rectificacionSunat.editar', compact('venta', 'clientes'));
    }

    public function rectificar(Request $request, $id)
    {
        // 1. VALIDACIÓN
        $request->validate([
            'cliente_id'    => 'required|exists:clientes,id',
            'fecha_emision' => 'required|date',
        ]);

        $venta = Venta::with(['detalles', 'sucursal'])->findOrFail($id);

        // Seguridad: No editar si ya está aceptada
        if ($venta->estado === 'ACEPTADA') {
            return back()->with('error', 'No se puede editar una venta ya ACEPTADA.');
        }

        try {
            DB::transaction(function () use ($venta, $request) {
                // A. ACTUALIZAR CABECERA (Cliente y Fecha)
                $venta->cliente_id = $request->cliente_id;
                $venta->fecha_emision = $request->fecha_emision;

                // B. RECALCULAR TOTALES (Como si fuera nueva venta)
                // Usamos la lógica de tu VentaService para asegurar coherencia

                $sucursal = $venta->sucursal;
                $esZonaAmazonia = ($sucursal->impuesto_porcentaje == 0);

                $nuevaOpGravada = 0;
                $nuevaOpExonerada = 0;
                $nuevoTotalIgv = 0;
                $nuevoTotalNeto = 0;

                foreach ($venta->detalles as $detalle) {
                    // Tomamos el precio de venta unitario original (incluye impuestos)
                    $precioVenta = $detalle->precio_unitario;
                    $cantidad = $detalle->cantidad;
                    $subtotalItem = $precioVenta * $cantidad;

                    // Determinamos si era exonerado o gravado basándonos en lo que se guardó
                    // OJO: Si quieres forzar re-evaluación de Amazonía:
                    $esExonerado = $esZonaAmazonia || ($detalle->tipo_afectacion == '20');

                    if ($esExonerado) {
                        // Lógica Exonerado (Cod 20)
                        $valorUnitario = $precioVenta;
                        $igvItemTotal = 0;

                        $detalle->tipo_afectacion = '20'; // Aseguramos el código
                        $nuevaOpExonerada += $subtotalItem;
                    } else {
                        // Lógica Gravado (Cod 10)
                        // Desglosamos el IGV matemático (Precio / 1.18)
                        $valorUnitario = $precioVenta / 1.18;
                        $igvItemTotal = ($precioVenta - $valorUnitario) * $cantidad;

                        $detalle->tipo_afectacion = '10'; // Aseguramos el código
                        $nuevaOpGravada += ($valorUnitario * $cantidad);
                        $nuevoTotalIgv += $igvItemTotal;
                    }

                    // Actualizamos el detalle en BD con los cálculos corregidos
                    $detalle->valor_unitario = $valorUnitario;
                    $detalle->igv = $igvItemTotal;
                    $detalle->subtotal_bruto = $valorUnitario * $cantidad; // Base imponible
                    $detalle->subtotal_neto = $subtotalItem; // Precio Final
                    $detalle->save();

                    $nuevoTotalNeto += $subtotalItem;
                }

                // C. APLICAR DESCUENTOS GLOBALES (Si existían)
                // Si la venta tenía descuento, hay que recalcular proporciones
                $descuentoDinero = $venta->total_descuento; // Mantenemos el descuento original

                if ($descuentoDinero > 0 && $nuevoTotalNeto > 0) {
                    $factorAjuste = (1 - ($descuentoDinero / $nuevoTotalNeto));

                    $nuevaOpGravada *= $factorAjuste;
                    $nuevaOpExonerada *= $factorAjuste;
                    $nuevoTotalIgv *= $factorAjuste;
                    // El neto final es Total - Descuento
                    $nuevoTotalNeto = $nuevoTotalNeto - $descuentoDinero;
                }

                $venta->op_gravada = round($nuevaOpGravada, 2);
                $venta->op_exonerada = round($nuevaOpExonerada, 2);
                $venta->total_igv = round($nuevoTotalIgv, 2);
                $venta->total_neto = round($nuevoTotalNeto, 2);
                $venta->total_bruto = round(($nuevaOpGravada + $nuevaOpExonerada) + $descuentoDinero, 2);
                $venta->save();

                $exito = $this->sunatService->transmitirAComprobante($venta);

                if (!$exito) {
                    throw new \Exception($venta->mensaje_sunat);
                }
            });

            return redirect()->route('facturacion.pendientes')
                ->with('success', '¡Rectificación Completa! Datos recalculados y comprobante aceptado.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al rectificar: ' . $e->getMessage());
        }
    }
}
