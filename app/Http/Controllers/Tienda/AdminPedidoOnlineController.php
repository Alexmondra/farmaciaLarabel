<?php

namespace App\Http\Controllers\Tienda;

use App\Http\Controllers\Controller;
use App\Models\Tienda\PedidoOnline;
use App\Models\Ventas\CajaSesion;
use App\Models\Ventas\Cliente;
use App\Models\Inventario\Lote;
use App\Services\SucursalResolver;
use App\Services\VentaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\ComprobanteMailable;

class AdminPedidoOnlineController extends Controller
{
    protected SucursalResolver $sucursalResolver;
    protected VentaService $ventaService;

    public function __construct(SucursalResolver $sucursalResolver, VentaService $ventaService)
    {
        $this->sucursalResolver = $sucursalResolver;
        $this->ventaService = $ventaService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $ctx = $this->sucursalResolver->resolverPara($user);

        $pedidos = PedidoOnline::with(['cliente', 'sucursal'])
            ->when($ctx['ids_filtro'], fn($query) => $query->whereIn('sucursal_id', $ctx['ids_filtro']))
            ->when($request->filled('estado'), fn($query) => $query->where('estado', $request->estado))
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = trim($request->q);
                $query->where(function ($sub) use ($term) {
                    $sub->where('codigo', 'LIKE', "%{$term}%")
                        ->orWhere('cliente_documento', 'LIKE', "%{$term}%")
                        ->orWhere('cliente_nombre', 'LIKE', "%{$term}%");
                });
            })
            ->when(!$request->filled('estado'), function ($q) {
                // Ordenar por fecha de recojo de forma ascendente cuando se muestren todos los estados
                $q->orderBy('fecha_recojo', 'asc');
            }, function ($q) {
                // Si hay filtro de estado, usar el orden tradicional
                $q->latest();
            })
            ->paginate(20)
            ->appends($request->query());

        if ($request->ajax()) {
            return response()->json([
                'html' => view('tienda.admin.pedidos.partials.table', compact('pedidos'))->render(),
            ]);
        }

        return view('tienda.admin.pedidos.index', compact('pedidos'));
    }

    public function buscarAjax(Request $request)
    {
        $q = trim($request->get('q'));
        if (!$q) {
            return response()->json(['success' => false, 'message' => 'No se proporcionó un término de búsqueda.'], 400);
        }

        // Si es una URL completa, extraer el token (último segmento después de /)
        if (filter_var($q, FILTER_VALIDATE_URL)) {
            $path = parse_url($q, PHP_URL_PATH);
            $segments = explode('/', rtrim($path, '/'));
            $q = end($segments);
        }

        // Buscar por qr_token o por codigo
        $pedido = PedidoOnline::with(['cliente', 'sucursal', 'detalles.medicamento'])
            ->where('qr_token', $q)
            ->orWhere('codigo', $q)
            ->first();

        if (!$pedido) {
            return response()->json(['success' => false, 'message' => 'Pedido no encontrado.'], 404);
        }

        // Validar si el pedido ya está entregado o anulado
        $warning = null;
        if ($pedido->estado === 'ENTREGADO') {
            $warning = 'Este pedido ya fue entregado y facturado.';
        } elseif ($pedido->estado === 'CANCELADO') {
            $warning = 'Este pedido está CANCELADO.';
        }

        // Retornar información detallada
        return response()->json([
            'success' => true,
            'warning' => $warning,
            'pedido' => [
                'id' => $pedido->id,
                'codigo' => $pedido->codigo,
                'cliente_nombre' => $pedido->cliente_nombre ?? ($pedido->cliente ? ($pedido->cliente->nombre . ' ' . $pedido->cliente->apellidos) : 'Cliente General'),
                'cliente_documento' => $pedido->cliente_documento ?? ($pedido->cliente->documento ?? ''),
                'cliente_id' => $pedido->cliente_id,
                'sucursal' => $pedido->sucursal->nombre ?? 'N/A',
                'sucursal_id' => $pedido->sucursal_id,
                'total' => (float) $pedido->total,
                'subtotal' => (float) $pedido->subtotal,
                'costo_envio' => (float) $pedido->costo_envio,
                'estado' => $pedido->estado,
                'estado_pago' => $pedido->estado_pago,
                'metodo_pago' => $pedido->metodo_pago,
                'tipo_entrega' => $pedido->tipo_entrega,
                'observaciones' => $pedido->observaciones,
                'fecha_recojo' => $pedido->fecha_recojo ? $pedido->fecha_recojo->format('d/m/Y H:i') : null,
                'entregado_at' => $pedido->entregado_at ? $pedido->entregado_at->format('d/m/Y H:i') : null,
                'venta_id' => $pedido->venta_id,
                'print_ticket_url' => $pedido->venta_id ? route('ventas.print_ticket', $pedido->venta_id) : null,
                'print_a4_url' => $pedido->venta_id ? route('ventas.print_a4', $pedido->venta_id) : null,
            ],
            'detalles' => $pedido->detalles->map(function ($d) {
                return [
                    'id' => $d->id,
                    'medicamento_id' => $d->medicamento_id,
                    'nombre' => $d->medicamento->nombre ?? $d->descripcion,
                    'presentacion' => $d->medicamento->presentacion ?? '',
                    'cantidad' => $d->cantidad,
                    'precio_unitario' => (float) $d->precio_unitario,
                    'subtotal' => (float) $d->subtotal,
                ];
            })
        ]);
    }

    public function show(PedidoOnline $pedido)
    {
        $pedido->load(['cliente', 'sucursal', 'detalles.medicamento', 'pagos', 'venta']);

        return view('tienda.admin.pedidos.show', compact('pedido'));
    }

    public function updateEstado(Request $request, PedidoOnline $pedido)
    {
        if (in_array($pedido->estado, ['ENTREGADO', 'CANCELADO', 'CONVERTIDO_A_VENTA'])) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No es posible modificar el estado de un pedido que ya ha sido entregado o cancelado.'
                ], 400);
            }
            return back()->with('error', 'No es posible modificar el estado de un pedido que ya ha sido entregado o cancelado.');
        }

        $data = $request->validate([
            'estado' => ['required', 'in:PENDIENTE,CONFIRMADO,PREPARANDO,LISTO,ENTREGADO,CANCELADO,CONVERTIDO_A_VENTA'],
        ]);

        $pedido->estado = $data['estado'];

        if ($data['estado'] === 'CONFIRMADO' && !$pedido->confirmado_at) {
            $pedido->confirmado_at = now();
        }

        if ($data['estado'] === 'ENTREGADO' && !$pedido->entregado_at) {
            $pedido->entregado_at = now();
        }

        $pedido->save();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'El estado del pedido ' . $pedido->codigo . ' ha sido actualizado a ' . $pedido->estado . '.',
                'estado' => $pedido->estado
            ]);
        }

        return back()->with('success', 'Estado del pedido actualizado.');
    }

    public function entregarYFacturar(Request $request, PedidoOnline $pedido)
    {
        $user = Auth::user();

        // 1. Validar que el pedido no esté ya entregado
        if ($pedido->estado === 'ENTREGADO') {
            return response()->json(['success' => false, 'message' => 'El pedido ya fue entregado.'], 400);
        }

        // 2. Validar que el usuario tenga caja abierta en la sucursal del pedido
        $caja = CajaSesion::where('user_id', $user->id)
            ->where('sucursal_id', $pedido->sucursal_id)
            ->where('estado', 'ABIERTO')
            ->first();

        if (!$caja) {
            return response()->json([
                'success' => false, 
                'message' => 'No tienes una caja abierta en la sucursal del pedido (' . ($pedido->sucursal->nombre ?? 'N/A') . '). Por favor, abre una caja antes de procesar la entrega.'
            ], 400);
        }

        // 3. Validar datos de facturación y pago
        $data = $request->validate([
            'tipo_comprobante' => ['required', 'string', 'in:BOLETA,FACTURA,TICKET'],
            'medio_pago' => ['required', 'string', 'in:EFECTIVO,TARJETA,YAPE,PLIN'],
            'paga_con' => ['nullable', 'numeric', 'min:0'],
            'cliente_ruc' => ['nullable', 'required_if:tipo_comprobante,FACTURA', 'string', 'digits:11'],
            'cliente_razon_social' => ['nullable', 'required_if:tipo_comprobante,FACTURA', 'string', 'max:255'],
        ]);

        try {
            $venta = DB::transaction(function () use ($pedido, $user, $caja, $data) {
                // 4. Asignación automática de lotes (PEPS) por medicamento
                $itemsVenta = [];
                $pedido->load('detalles.medicamento');

                foreach ($pedido->detalles as $detalle) {
                    $cantidadRequerida = $detalle->cantidad;
                    
                    // Buscar lotes con stock en la sucursal del pedido, ordenados por vencimiento (PEPS)
                    $lotes = Lote::where('medicamento_id', $detalle->medicamento_id)
                        ->where('sucursal_id', $pedido->sucursal_id)
                        ->where('stock_actual', '>', 0)
                        ->orderBy('fecha_vencimiento', 'asc')
                        ->get();

                    $stockTotalDisponible = $lotes->sum('stock_actual');
                    if ($stockTotalDisponible < $cantidadRequerida) {
                        throw new \Exception('Stock insuficiente para el medicamento: ' . $detalle->descripcion . '. Requerido: ' . $cantidadRequerida . ', Disponible en lotes: ' . $stockTotalDisponible);
                    }

                    $cantidadRestante = $cantidadRequerida;
                    foreach ($lotes as $lote) {
                        if ($cantidadRestante <= 0) break;

                        $cantidadATomar = min($lote->stock_actual, $cantidadRestante);
                        
                        $itemsVenta[] = [
                            'id' => $lote->id, // VentaService usa 'id' como lote_id
                            'medicamento_id' => $detalle->medicamento_id,
                            'cantidad' => $cantidadATomar,
                            'unidad_medida' => 'UNIDAD',
                            'precio_venta' => (float) $detalle->precio_unitario,
                        ];

                        $cantidadRestante -= $cantidadATomar;
                    }
                }

                // 5. Manejo del cliente
                $clienteId = $pedido->cliente_id;

                // Si es factura y se ingresó un RUC diferente al documento del cliente del pedido
                if ($data['tipo_comprobante'] === 'FACTURA') {
                    $ruc = $data['cliente_ruc'];
                    $razonSocial = $data['cliente_razon_social'];

                    // Buscar si ya existe un cliente con ese RUC
                    $clienteFactura = Cliente::where('documento', $ruc)->first();

                    if (!$clienteFactura) {
                        // Crear nuevo cliente de tipo empresa/RUC
                        $clienteFactura = Cliente::create([
                            'tipo_documento' => 'RUC',
                            'documento' => $ruc,
                            'nombre' => $razonSocial,
                            'apellidos' => '',
                            'activo' => true
                        ]);
                    }
                    $clienteId = $clienteFactura->id;
                }

                // 6. Preparar los datos para registrar la venta en VentaService
                $ventaData = [
                    'caja_sesion_id' => $caja->id,
                    'cliente_id' => $clienteId,
                    'tipo_comprobante' => $data['tipo_comprobante'],
                    'medio_pago' => $data['medio_pago'],
                    'items' => json_encode($itemsVenta),
                    'paga_con' => $data['paga_con'] ?? $pedido->total,
                    'referencia_pago' => 'PEDIDO-' . $pedido->codigo,
                ];

                // Registrar venta
                $ventaResult = $this->ventaService->registrarVenta($user, $ventaData);

                // 7. Actualizar el pedido online
                $updateData = [
                    'venta_id' => $ventaResult->id,
                    'estado' => 'ENTREGADO',
                    'estado_pago' => 'PAGADO',
                    'entregado_at' => now(),
                ];

                if ($pedido->estado_pago !== 'PAGADO' && $pedido->estado_pago !== 'COMPLETADO') {
                    $updateData['metodo_pago'] = 'PAGO_AL_RECOGER';
                }

                $pedido->update($updateData);

                return $ventaResult;
            });

            if ($pedido->cliente_email) {
                try {
                    Mail::to($pedido->cliente_email)->queue(new ComprobanteMailable($venta));
                } catch (\Exception $e) {
                    Log::error('Error al enviar correo de comprobante automático en Pedido Online: ' . $e->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Pedido ' . $pedido->codigo . ' entregado y comprobante generado con éxito.',
                'venta_id' => $venta->id,
                'print_ticket_url' => route('ventas.print_ticket', $venta->id),
                'print_a4_url' => route('ventas.print_a4', $venta->id),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la entrega: ' . $e->getMessage()
            ], 500);
        }
    }
}
