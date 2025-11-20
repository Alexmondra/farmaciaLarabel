<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;  // <-- ¡Importante!
use App\Services\SucursalResolver;
use App\Models\Ventas\Venta;
use App\Models\Ventas\CajaSesion;
use App\Models\Ventas\Cliente;      // <-- Nuevo
use App\Models\Inventario\Lote;     // <-- Nuevo
use App\Models\Sucursal;
use App\Models\Inventario\MedicamentoSucursal;
use App\Models\Inventario\Categoria;


class VentaController extends Controller
{
    protected SucursalResolver $sucursalResolver;

    public function __construct(SucursalResolver $sucursalResolver)
    {
        $this->sucursalResolver = $sucursalResolver;
    }

    /**
     * Muestra el índice de Ventas.
     * Solo muestra ventas si hay una caja ABIERTA.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $ctx = $this->sucursalResolver->resolverPara($user);

        // 1. *** LÓGICA CLAVE ***
        // Buscar la sesión de caja ABIERTA del usuario
        // que coincida con el contexto de sucursal actual.

        $cajaAbiertaQuery = CajaSesion::where('user_id', $user->id)
            ->where('estado', 'ABIERTO');

        // Aplicamos el filtro de sucursal del resolver
        if ($ctx['ids_filtro'] !== null) {
            $cajaAbiertaQuery->whereIn('sucursal_id', $ctx['ids_filtro']);
        }

        // Buscamos la caja (solo puede ser una o ninguna)
        $cajaAbierta = $cajaAbiertaQuery->with('sucursal')->first();

        // 2. Obtener Ventas (solo si la caja existe)
        $ventas = [];
        if ($cajaAbierta) {
            $ventas = Venta::where('caja_sesion_id', $cajaAbierta->id)
                ->with(['cliente', 'usuario']) // Cargamos relaciones
                ->orderBy('fecha_emision', 'desc')
                ->paginate(20);
        }

        // 3. Cargar sucursales PARA EL MODAL DE APERTURA
        // Esto se hace SIEMPRE, por si necesita abrir caja.
        $sucursalesParaApertura = $ctx['es_admin']
            ? Sucursal::orderBy('nombre')->get()
            : $user->sucursales()->select('sucursales.*')->orderBy('sucursales.nombre')->get();

        // 4. Mandar a la vista
        return view('ventas.ventas.index', [
            'cajaAbierta'            => $cajaAbierta, // La vista sabe si mostrar o no
            'ventas'                 => $ventas,      // Lista de ventas (o array vacío)
            'sucursalesParaApertura' => $sucursalesParaApertura,
            'esAdmin'                => $ctx['es_admin'],
            'sucursalSeleccionada'   => $ctx['sucursal_seleccionada'],
        ]);
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        $ctx = $this->sucursalResolver->resolverPara($user);

        // 1. Verificar si hay caja abierta (requisito indispensable)
        $cajaAbierta = CajaSesion::where('user_id', $user->id)
            ->where('estado', 'ABIERTO')
            ->when($ctx['ids_filtro'], function ($q) use ($ctx) {
                $q->whereIn('sucursal_id', $ctx['ids_filtro']);
            })
            ->first();

        // 2. Si no hay caja, redirigir al index (que le mostrará el modal)
        if (!$cajaAbierta) {
            return redirect()->route('ventas.index')
                ->with('error', 'Debes abrir una caja antes de poder registrar una venta.');
        }

        // 3. Cargar clientes para el dropdown
        $categorias = Categoria::select('id', 'nombre')->orderBy('nombre')->get();

        // 4. Mandar a la vista
        return view('ventas.ventas.create', [
            'cajaAbierta' => $cajaAbierta,
            'categorias'    => $categorias,
        ]);
    }


    /**
     * Guarda la Venta, Detalles y descuenta Stock de Lotes.
     * NUEVO MÉTODO
     */
    /**
     * Guarda la Venta, Detalles y descuenta Stock de Lotes.
     * OPTIMIZADO: Con validación previa "Fail Fast".
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // 1. Validación de formulario (Datos básicos)
        $data = $request->validate([
            'caja_sesion_id'   => ['required', 'integer', 'exists:caja_sesiones,id'],
            'cliente_id'       => ['required', 'integer', 'exists:clientes,id'],
            'tipo_comprobante' => ['required', 'string', 'in:BOLETA,FACTURA,TICKET'],
            'medio_pago'       => ['required', 'string', 'in:EFECTIVO,TARJETA,YAPE,PLIN'],
            'items'            => ['required', 'string'], // JSON string
        ]);

        $items = json_decode($data['items'], true);

        if (empty($items)) {
            return back()->withErrors('No se puede registrar una venta sin productos.');
        }

        // =========================================================================
        // 2. OPTIMIZACIÓN: PRE-CHEQUEO "FAIL FAST" (Sin bloqueo)
        // =========================================================================
        // Consultamos el stock actual sin bloquear la fila.
        // Si un usuario pide 10 y hay 2, rechazamos aquí y ahorramos abrir la transacción.
        foreach ($items as $item) {
            // Usamos DB::table o select() ligero para no hidratar el modelo completo
            $loteInfo = DB::table('lotes')
                ->select('stock_actual', 'codigo_lote')
                ->where('id', $item['lote_id'])
                ->first();

            if (!$loteInfo) {
                return back()->withErrors("El lote seleccionado ya no existe (Item: {$item['nombre']}).");
            }

            if ($loteInfo->stock_actual < $item['cantidad']) {
                return back()->withErrors(
                    "Stock insuficiente (Pre-validación) para: {$item['nombre']}. " .
                        "Lote: {$loteInfo->codigo_lote}. Disponible: {$loteInfo->stock_actual}"
                )->withInput();
            }
        }
        // =========================================================================


        try {
            // 3. INICIO DE LA TRANSACCIÓN REAL (Aquí sí bloqueamos)
            $venta = DB::transaction(function () use ($data, $items, $user) {

                // A. Buscar la caja y bloquearla (lockForUpdate)
                // Validamos que siga abierta y pertenezca al usuario
                $caja = CajaSesion::where('id', $data['caja_sesion_id'])
                    ->where('user_id', $user->id)
                    ->where('estado', 'ABIERTO')
                    ->lockForUpdate()
                    ->firstOrFail();

                $totalBruto = 0;
                $totalNeto  = 0; // Asumimos descuento 0 por ahora
                $detallesParaGuardar = [];

                // B. Recorrer items y procesar (Esta vez con seguridad atómica)
                foreach ($items as $item) {
                    $cantidadVenta = (int)$item['cantidad'];

                    // Buscamos el lote y lo BLOQUEAMOS para que nadie más lo toque
                    $lote = Lote::where('id', $item['lote_id'])
                        ->where('sucursal_id', $caja->sucursal_id)
                        ->lockForUpdate() // <--- CLAVE
                        ->firstOrFail();

                    // C. Re-validar Stock (La validación definitiva)
                    // Aunque validamos arriba, el stock pudo cambiar en esos milisegundos.
                    // Esta validación es la que manda.
                    if ($lote->stock_actual < $cantidadVenta) {
                        throw new \Exception(
                            "Stock insuficiente durante transacción para: " . $item['nombre'] .
                                " (Lote: {$lote->codigo_lote}). Quedan: {$lote->stock_actual}"
                        );
                    }

                    // D. Descontar Stock
                    $lote->decrement('stock_actual', $cantidadVenta);
                    // Opcional: Si llega a 0, podrías cambiar estado del lote, etc.

                    // E. Cálculos monetarios
                    $precio = (float)$item['precio_venta'];
                    $subtotal = $cantidadVenta * $precio;

                    // Preparar objeto Detalle (sin guardarlo todavía para optimizar queries)
                    $detallesParaGuardar[] = new \App\Models\Ventas\DetalleVenta([
                        'lote_id'         => $lote->id,
                        'medicamento_id'  => $lote->medicamento_id,
                        'cantidad'        => $cantidadVenta,
                        'precio_unitario' => $precio,
                        'subtotal_bruto'  => $subtotal,
                        'subtotal_neto'   => $subtotal, // Ajustar si manejas descuentos por ítem
                    ]);

                    $totalNeto += $subtotal;
                }

                // F. Crear la Cabecera de Venta
                // Nota: Para el número correlativo, idealmente usa una tabla separada de series
                // Aquí usamos una lógica simple (max + 1) que funciona dentro del lock de transacción.
                $ultimoNumero = Venta::where('tipo_comprobante', $data['tipo_comprobante'])
                    ->max('numero') ?? 0;

                $nuevaVenta = Venta::create([
                    'caja_sesion_id'   => $caja->id,
                    'sucursal_id'      => $caja->sucursal_id,
                    'cliente_id'       => $data['cliente_id'],
                    'user_id'          => $user->id,
                    'tipo_comprobante' => $data['tipo_comprobante'],
                    'serie'            => 'B001', // Deberías dinamizar esto según la caja/sucursal
                    'numero'           => $ultimoNumero + 1,
                    'fecha_emision'    => now(),
                    'total_bruto'      => $totalNeto,
                    'total_descuento'  => 0,
                    'total_neto'       => $totalNeto,
                    'medio_pago'       => $data['medio_pago'],
                    'estado'           => 'COMPLETADA',
                ]);

                // G. Guardar todos los detalles de golpe
                $nuevaVenta->detalles()->saveMany($detallesParaGuardar);

                return $nuevaVenta;
            });
            // --- FIN TRANSACCIÓN ---

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Error específico si no encuentra la caja o un lote al bloquear
            return back()->withErrors(['general' => 'Error de sincronización: Un lote o la caja ya no está disponible.'])->withInput();
        } catch (\Exception $e) {
            // Cualquier otro error (Stock insuficiente dentro del lock, etc.)
            return back()->withErrors(['general' => $e->getMessage()])->withInput();
        }

        // 4. Éxito
        return redirect()->route('ventas.show', $venta->id)
            ->with('success', 'Venta registrada correctamente.');
    }

    /**
     * Busca lotes disponibles para un medicamento en una sucursal.
     * MÉTODO AJAX
     */


    public function lookupMedicamentos(Request $request)
    {
        $request->validate([
            'sucursal_id'  => ['required', 'integer', 'exists:sucursales,id'],
            'q'            => ['nullable', 'string', 'max:100'],
            'categoria_id' => ['nullable', 'integer'],
        ]);

        $hoy = now()->format('Y-m-d');

        $query = MedicamentoSucursal::with('medicamento')
            ->where('sucursal_id', $request->sucursal_id)
            ->activos() // scopeActivos del modelo MedicamentoSucursal
            // 🔹 Solo medicamentos con al menos un lote disponible en esa sucursal
            ->whereExists(function ($sub) use ($request, $hoy) {
                $sub->selectRaw(1)
                    ->from('lotes')
                    ->whereColumn('lotes.medicamento_id', 'medicamento_sucursal.medicamento_id')
                    ->where('lotes.sucursal_id', $request->sucursal_id)
                    ->where('lotes.stock_actual', '>', 0)
                    ->where(function ($q2) use ($hoy) {
                        $q2->whereDate('lotes.fecha_vencimiento', '>=', $hoy)
                            ->orWhereNull('lotes.fecha_vencimiento');
                    });
            });

        if ($request->filled('q')) {
            // scopeBuscar: busca por nombre o código del medicamento
            $query->buscar($request->q);
        }

        if ($request->filled('categoria_id')) {
            $categoriaId = $request->categoria_id;
            $query->whereHas('medicamento', function ($q) use ($categoriaId) {
                $q->where('categoria_id', $categoriaId);
            });
        }

        $medicamentos = $query->orderBy('id', 'desc')
            ->limit(30)
            ->get()
            ->map(function ($ms) {
                return [
                    'medicamento_sucursal_id' => $ms->id,
                    'medicamento_id'          => $ms->medicamento_id,
                    'nombre'                  => $ms->medicamento->nombre,
                    'codigo'                  => $ms->medicamento->codigo,
                    'presentacion'            => $ms->medicamento->presentacion ?? null,
                    'precio_venta'            => (float) $ms->precio_venta,
                ];
            });

        return response()->json($medicamentos);
    }

    public function lookupLotes(Request $request)
    {
        $request->validate([
            'medicamento_id' => 'required|integer',
            'sucursal_id'    => 'required|integer',
        ]);

        $hoy = now()->format('Y-m-d');

        // 1. Obtenemos el precio BASE de la sucursal primero (para no depender de relaciones complejas en Lote)
        $precioBase = MedicamentoSucursal::where('medicamento_id', $request->medicamento_id)
            ->where('sucursal_id', $request->sucursal_id)
            ->value('precio_venta'); // Obtiene solo el valor float directo

        $precioBase = $precioBase ? (float)$precioBase : 0.00;

        // 2. Buscamos los lotes
        $lotes = Lote::where('medicamento_id', $request->medicamento_id)
            ->where('sucursal_id', $request->sucursal_id)
            ->where('stock_actual', '>', 0)
            ->where(function ($q) use ($hoy) {
                $q->whereDate('fecha_vencimiento', '>=', $hoy)
                    ->orWhereNull('fecha_vencimiento');
            })
            ->orderBy('fecha_vencimiento', 'asc') // Prioridad: Vencimiento más próximo primero (FIFO/FEFO)
            ->get()
            ->map(function ($lote) use ($precioBase) {
                return [
                    'id'                => $lote->id,
                    'codigo_lote'       => $lote->codigo_lote,
                    'fecha_vencimiento' => optional($lote->fecha_vencimiento)->format('Y-m-d'),
                    'ubicacion'         => $lote->ubicacion,
                    'stock_actual'      => $lote->stock_actual,

                    // Usamos el precio base que buscamos arriba
                    'precio_venta'      => $precioBase,

                    // Si el lote tiene oferta específica, la mandamos
                    'precio_oferta'     => $lote->precio_oferta !== null
                        ? (float) $lote->precio_oferta
                        : null,
                ];
            });

        return response()->json($lotes);
    }
}
