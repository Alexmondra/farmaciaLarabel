<?php

namespace App\Http\Controllers\Compras;

use App\Http\Controllers\Controller;
use App\Models\Compras\Compra;
use App\Models\Inventario\Lote;
use App\Models\Compras\Proveedor;
use App\Models\Inventario\Medicamento;
use App\Models\Inventario\Categoria;

use App\Models\Compras\DetalleCompra;
use App\Repositories\CompraRepository;
use App\Services\SucursalResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Inventario\MedicamentoSucursal;


use Illuminate\Support\Facades\DB;

class CompraController extends Controller
{
    protected CompraRepository $compras;
    protected SucursalResolver $sucursalResolver;

    public function __construct(CompraRepository $compras, SucursalResolver $sucursalResolver)
    {
        $this->compras          = $compras;
        $this->sucursalResolver = $sucursalResolver;

        $this->middleware('auth');
    }

    /**
     * LISTADO de compras
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $ctx  = $this->sucursalResolver->resolverPara($user);

        $idsFiltro = $ctx['ids_filtro']; // null | [] | [id,...]

        $query = Compra::query()
            ->with(['proveedor', 'sucursal', 'detalles'])
            ->orderByDesc('fecha_recepcion')
            ->orderByDesc('id');

        if (is_array($idsFiltro) && count($idsFiltro) > 0) {
            $query->whereIn('sucursal_id', $idsFiltro);
        }

        $compras = $query->paginate(20)->withQueryString();

        return view('inventario.compras.index', [
            'compras'              => $compras,
            'sucursalSeleccionada' => $ctx['sucursal_seleccionada'],
            'esAdmin'              => $ctx['es_admin'],
        ]);
    }

    /**
     * VER detalle de compra
     */
    public function show($id)
    {
        $user = Auth::user();
        $ctx  = $this->sucursalResolver->resolverPara($user);
        $idsFiltro = $ctx['ids_filtro'];

        // 👇 Eager load con lote y medicamento del lote
        $compra = Compra::with([
            'proveedor',
            'sucursal',
            'detalles.lote.medicamento',
        ])->findOrFail($id);

        // Seguridad básica por sucursal
        if (is_array($idsFiltro) && count($idsFiltro) > 0 && !in_array($compra->sucursal_id, $idsFiltro)) {
            abort(403, 'No estás autorizado para ver esta compra.');
        }

        // 👇 Total usando cantidad_recibida
        $total = $compra->detalles->sum(function ($d) {
            return $d->cantidad_recibida * $d->precio_compra_unitario;
        });

        return view('inventario.compras.show', [
            'compra'              => $compra,
            'total'               => $total,
            'sucursalSeleccionada' => $ctx['sucursal_seleccionada'],
            'esAdmin'             => $ctx['es_admin'],
        ]);
    }

    /**
     * FORM crear compra
     */
    public function create()
    {
        $user = Auth::user();
        $ctx  = $this->sucursalResolver->resolverPara($user);

        if (!$ctx['sucursal_seleccionada']) {
            return redirect()->route('compras.index')
                ->with('error', 'Selecciona una sucursal.');
        }

        $sucursalId = $ctx['sucursal_seleccionada']->id;
        $proveedores = Proveedor::orderBy('razon_social')->get();

        // MODIFICACIÓN: Cargamos la relación 'sucursales' filtrada por la sucursal actual
        // para poder sacar el precio_venta actual en la vista.
        $medicamentos = Medicamento::with(['sucursales' => function ($q) use ($sucursalId) {
            $q->where('sucursal_id', $sucursalId);
        }])->orderBy('nombre')->get();
        $categorias = Categoria::orderBy('nombre')->get();
        return view('inventario.compras.create', [
            'categorias' => $categorias,
            'proveedores'          => $proveedores,
            'medicamentos'         => $medicamentos,
            'sucursalSeleccionada' => $ctx['sucursal_seleccionada'],
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $ctx  = $this->sucursalResolver->resolverPara($user);
        $sucursalSeleccionada = $ctx['sucursal_seleccionada'];

        if (!$sucursalSeleccionada) return redirect()->back()->with('error', 'Seleccione una sucursal.');

        // 1. VALIDACIÓN
        $data = $request->validate([
            // Cabecera
            'proveedor_id'             => 'required|exists:proveedores,id',
            'fecha_recepcion'          => 'required|date',
            'tipo_comprobante'         => 'nullable|string',
            'numero_factura_proveedor' => 'nullable|string',
            'observaciones'            => 'nullable|string',
            'archivo_comprobante'      => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png', // Max 10MB

            // Items (Arrays)
            'items'                            => 'required|array|min:1',
            'items.*.medicamento_id'           => 'required|integer',
            'items.*.codigo_lote'              => 'required|string', // Lote obligatorio
            'items.*.fecha_vencimiento'        => 'required|date',   // Vencimiento obligatorio

            // Cantidades y Costos (Calculados por JS)
            'items.*.cantidad_recibida'        => 'required|integer|min:1',
            'items.*.precio_compra_unitario'   => 'required|numeric|min:0',

            // Precios de Venta (Nuevos campos)
            'items.*.precio_venta'             => 'required|numeric|min:0',      // Unidad
            'items.*.precio_venta_blister'     => 'nullable|numeric|min:0',      // Blíster
            'items.*.precio_venta_caja'        => 'nullable|numeric|min:0',      // Caja
            'items.*.precio_oferta'            => 'nullable|numeric|min:0',      // Oferta

            'items.*.ubicacion'                => 'nullable|string',
        ]);

        // 2. ARCHIVO PRIVADO (Seguridad)
        $pathArchivo = null;
        if ($request->hasFile('archivo_comprobante')) {
            // Guardamos en el disco 'local' (storage/app), NO en 'public'.
            // Carpeta: compras_documentos
            $pathArchivo = $request->file('archivo_comprobante')->store('compras_documentos', 'local');
        }

        // 3. TRANSACCIÓN DB
        $compra = DB::transaction(function () use ($data, $user, $sucursalSeleccionada, $pathArchivo) {

            // A. Crear Cabecera
            $compra = Compra::create([
                'proveedor_id'             => $data['proveedor_id'],
                'sucursal_id'              => $sucursalSeleccionada->id,
                'user_id'                  => $user->id,
                'numero_factura_proveedor' => $data['numero_factura_proveedor'],
                'fecha_recepcion'          => $data['fecha_recepcion'],
                'tipo_comprobante'         => $data['tipo_comprobante'],
                'observaciones'            => $data['observaciones'],
                'archivo_comprobante'      => $pathArchivo, // Guardamos la ruta privada
                'estado'                   => 'registrada',
                'costo_total_factura'      => 0
            ]);

            $totalCompra = 0;

            // B. Procesar Items
            foreach ($data['items'] as $item) {

                // 1. Crear Lote (Stock entra en UNIDADES TOTALES)
                $lote = Lote::create([
                    'medicamento_id'    => $item['medicamento_id'],
                    'sucursal_id'       => $sucursalSeleccionada->id,
                    'codigo_lote'       => strtoupper($item['codigo_lote']),
                    'stock_actual'      => $item['cantidad_recibida'],
                    'fecha_vencimiento' => $item['fecha_vencimiento'],
                    'ubicacion'         => $item['ubicacion'] ?? null,
                    'precio_compra'     => $item['precio_compra_unitario'],
                    'precio_oferta'     => $item['precio_oferta'] ?? null,
                ]);

                // 2. Detalle Compra (Histórico)
                DetalleCompra::create([
                    'compra_id'              => $compra->id,
                    'lote_id'                => $lote->id,
                    'cantidad_recibida'      => $item['cantidad_recibida'],
                    'precio_compra_unitario' => $item['precio_compra_unitario'],
                ]);

                // 3. Actualizar Precios en la Sucursal (Pivote)
                $ms = MedicamentoSucursal::withTrashed()->firstOrNew([
                    'medicamento_id' => $item['medicamento_id'],
                    'sucursal_id'    => $sucursalSeleccionada->id
                ]);

                if ($ms->trashed()) $ms->restore();

                // Actualizamos TODOS los precios configurados
                $ms->precio_venta   = $item['precio_venta'];
                $ms->precio_blister = $item['precio_venta_blister'] ?? null;
                $ms->precio_caja    = $item['precio_venta_caja'] ?? null; // <--- Nuevo campo Caja

                $ms->activo         = true;
                $ms->save();

                // Sumar al total de la factura
                $totalCompra += ($item['cantidad_recibida'] * $item['precio_compra_unitario']);
            }

            // Actualizar total final cabecera
            $compra->update(['costo_total_factura' => $totalCompra]);

            return $compra;
        });

        return redirect()->route('compras.show', $compra->id)
            ->with('success', 'Compra registrada correctamente.');
    }

    /**
     * NUEVO MÉTODO: Descargar archivo privado
     */
    public function descargarComprobante($id)
    {
        $compra = Compra::findOrFail($id);

        // Seguridad: Verificar si el usuario tiene permiso o pertenece a la sucursal
        // ... (Tu lógica de permisos aquí) ...

        if (!$compra->archivo_comprobante) {
            abort(404, 'No hay archivo adjunto.');
        }

        // Descarga segura desde storage/app/compras_documentos
        return response()->download(storage_path("app/{$compra->archivo_comprobante}"));
    }


    /**
     * FORM editar SOLO cabecera (no detalles)
     */
    public function edit($id)
    {
        $user = Auth::user();
        $ctx  = $this->sucursalResolver->resolverPara($user);
        $idsFiltro = $ctx['ids_filtro'];

        $compra = Compra::with('proveedor', 'sucursal')->findOrFail($id);

        if (is_array($idsFiltro) && count($idsFiltro) > 0 && !in_array($compra->sucursal_id, $idsFiltro)) {
            abort(403);
        }

        $proveedores = Proveedor::orderBy('nombre')->get();

        return view('compras.edit', [
            'compra'              => $compra,
            'proveedores'         => $proveedores,
            'sucursalSeleccionada' => $ctx['sucursal_seleccionada'],
            'esAdmin'             => $ctx['es_admin'],
        ]);
    }

    /**
     * UPDATE cabecera de compra (no toca stock ni lotes)
     */
    public function update(Request $request, $id)
    {
        $compra = Compra::findOrFail($id);

        $data = $request->validate([
            'proveedor_id'   => 'required|exists:proveedores,id',
            'fecha_compra'   => 'required|date',
            'tipo_documento' => 'required|string|max:20',
            'serie'          => 'nullable|string|max:10',
            'numero'         => 'nullable|string|max:20',
            'observacion'    => 'nullable|string|max:500',
            'estado'         => 'required|string|max:20',
        ]);

        $compra->update($data);

        return redirect()
            ->route('compras.show', $compra->id)
            ->with('success', 'Compra actualizada correctamente.');
    }

    /**
     * "Eliminar" compra → marcar como ANULADA (no borrar físico)
     */
    public function destroy($id)
    {
        $compra = Compra::with('detalles')->findOrFail($id);

        // Solo marcamos estado, no revertimos stock en esta versión
        $compra->estado = 'anulada';
        $compra->save();

        return redirect()
            ->route('compras.index')
            ->with('success', 'Compra anulada (no se ha borrado físicamente).');
    }
}
