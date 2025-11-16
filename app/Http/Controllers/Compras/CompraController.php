<?php

namespace App\Http\Controllers\Compras;

use App\Http\Controllers\Controller;
use App\Models\Compras\Compra;
use App\Models\Inventario\Lote;
use App\Models\Compras\Proveedor;
use App\Models\Inventario\Medicamento;
use App\Models\Compras\DetalleCompra;
use App\Repository\CompraRepository;
use App\Services\SucursalResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        // Requerimos una sucursal activa para registrar compra
        if (!$ctx['sucursal_seleccionada']) {
            return redirect()
                ->route('compras.index')
                ->with('error', 'Debes seleccionar una sucursal antes de registrar una compra.');
        }

        $proveedores  = Proveedor::orderBy('razon_social')->get();
        $medicamentos = Medicamento::orderBy('nombre')->get();

        return view('inventario.compras.create', [
            'proveedores'          => $proveedores,
            'medicamentos'         => $medicamentos,
            'sucursalSeleccionada' => $ctx['sucursal_seleccionada'],
        ]);
    }





    /**
     * GUARDAR compra + detalles + lotes
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $ctx  = $this->sucursalResolver->resolverPara($user);
        $sucursalSeleccionada = $ctx['sucursal_seleccionada'];

        if (!$sucursalSeleccionada) {
            return redirect()
                ->back()
                ->with('error', 'Debes seleccionar una sucursal para registrar esta compra.');
        }

        // 1) VALIDACIÓN DE CABECERA + ITEMS
        $data = $request->validate([
            'proveedor_id'              => 'required|exists:proveedores,id',
            'fecha_recepcion'           => 'required|date',
            'tipo_comprobante'          => 'nullable|string|max:30',
            'numero_factura_proveedor'  => 'nullable|string|max:100',
            'costo_total_factura'       => 'nullable|numeric|min:0', // lo recalculamos igual
            'observaciones'             => 'nullable|string|max:500',
            'archivo_comprobante'       => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',

            'items'                             => 'required|array|min:1',
            'items.*.medicamento_id'           => 'required|exists:medicamentos,id',
            'items.*.codigo_lote'              => 'nullable|string|max:80',
            'items.*.fecha_vencimiento'        => 'nullable|date',
            'items.*.cantidad_recibida'        => 'required|integer|min:1',
            'items.*.precio_compra_unitario'   => 'required|numeric|min:0',
            'items.*.precio_oferta'            => 'nullable|numeric|min:0',
            'items.*.ubicacion'                => 'nullable|string|max:100',
        ]);

        // 2) SUBIDA DE ARCHIVO (SI HAY)
        $pathArchivo = null;
        if ($request->hasFile('archivo_comprobante')) {
            $pathArchivo = $request->file('archivo_comprobante')
                ->store('compras', 'public'); // storage/app/public/compras
        }

        // 3) TRANSACCIÓN: COMPRA + LOTES + DETALLES
        $compra = DB::transaction(function () use ($data, $user, $sucursalSeleccionada, $pathArchivo) {

            // 3.1 Crear CABECERA de compra
            $compra = Compra::create([
                'proveedor_id'             => $data['proveedor_id'],
                'sucursal_id'              => $sucursalSeleccionada->id,
                'user_id'                  => $user->id,
                'numero_factura_proveedor' => $data['numero_factura_proveedor'] ?? null,
                'fecha_recepcion'          => $data['fecha_recepcion'],
                'costo_total_factura'      => 0, // se recalcula abajo
                'observaciones'            => $data['observaciones'] ?? null,
                'estado'                   => 'recibida',
                'tipo_comprobante'         => $data['tipo_comprobante'] ?? null,
                'archivo_comprobante'      => $pathArchivo,
            ]);

            $total = 0;

            // 3.2 Recorrer ITEMS → crear LOTE + DETALLE
            foreach ($data['items'] as $item) {

                // 3.2.1 Crear LOTE
                $lote = Lote::create([
                    'medicamento_id'    => $item['medicamento_id'],
                    'sucursal_id'       => $sucursalSeleccionada->id,
                    'codigo_lote'       => $item['codigo_lote'] ?? null,
                    'stock_actual'      => $item['cantidad_recibida'], // entra todo como stock
                    'fecha_vencimiento' => $item['fecha_vencimiento'] ?? null,
                    'ubicacion'         => $item['ubicacion'] ?? null,
                    'precio_compra'     => $item['precio_compra_unitario'],
                    'precio_oferta'     => $item['precio_oferta'] ?? null,
                    'observaciones'     => null,
                ]);

                // 3.2.2 Crear DETALLE_COMPRA
                DetalleCompra::create([
                    'compra_id'              => $compra->id,
                    'lote_id'                => $lote->id,
                    'cantidad_recibida'      => $item['cantidad_recibida'],
                    'precio_compra_unitario' => $item['precio_compra_unitario'],
                ]);

                // 3.2.3 Acumular total
                $total += $item['cantidad_recibida'] * $item['precio_compra_unitario'];

                // 3.2.4 (Opcional) registrar movimiento de inventario aquí
                // MovimientoInventario::create([...]);
            }

            // 3.3 Actualizar total calculado
            $compra->update([
                'costo_total_factura' => $total,
            ]);

            return $compra;
        });

        // 4) REDIRECCIÓN
        return redirect()
            ->route('compras.show', $compra->id)
            ->with('success', 'Compra registrada y stock actualizado correctamente.');
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
