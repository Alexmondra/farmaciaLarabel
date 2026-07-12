<?php

namespace App\Http\Controllers\Tienda;

use App\Http\Controllers\Controller;
use App\Models\Inventario\Categoria;
use App\Models\Sucursal;
use App\Models\Tienda\TiendaProducto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TiendaController extends Controller
{
    public function index(Request $request)
    {
        $sucursalIds = TiendaProducto::query()
            ->where('visible', true)
            ->whereNotNull('sucursal_id')
            ->distinct()
            ->pluck('sucursal_id');

        $categoriaIds = TiendaProducto::query()
            ->join('medicamentos', 'medicamentos.id', '=', 'tienda_productos.medicamento_id')
            ->where('tienda_productos.visible', true)
            ->whereNotNull('medicamentos.categoria_id')
            ->distinct()
            ->pluck('medicamentos.categoria_id');

        $sucursales = Sucursal::whereIn('id', $sucursalIds)->orderBy('nombre')->get();
        $categorias = Categoria::whereIn('id', $categoriaIds)->orderBy('nombre')->get();

        $productos = TiendaProducto::with(['medicamento.categoria', 'medicamento.sucursales', 'sucursal', 'imagenesVisibles'])
            ->where('visible', true)
            ->when($request->filled('sucursal'), function ($query) use ($request) {
                $query->where('sucursal_id', $request->integer('sucursal'));
            })
            ->when($request->filled('categoria'), function ($query) use ($request) {
                $query->whereHas('medicamento', function ($medicamento) use ($request) {
                    $medicamento->where('categoria_id', $request->integer('categoria'));
                });
            })
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = trim($request->q);
                $query->where(function ($sub) use ($term) {
                    $sub->where('nombre_web', 'LIKE', "%{$term}%")
                        ->orWhereHas('medicamento', function ($medicamento) use ($term) {
                            $medicamento->where('nombre', 'LIKE', "%{$term}%")
                                ->orWhere('laboratorio', 'LIKE', "%{$term}%")
                                ->orWhere('codigo', 'LIKE', "%{$term}%");
                        });
                });
            })
            ->orderByDesc('destacado')
            ->orderByDesc('id')
            ->orderBy('nombre_web')
            ->paginate(12)
            ->appends($request->query());

        if ($request->ajax()) {
            return response()->json([
                'html' => view('tienda.partials.product-cards', compact('productos'))->render(),
                'next_page_url' => $productos->nextPageUrl(),
            ]);
        }

        return view('tienda.index', compact('productos', 'sucursales', 'categorias'));
    }

    public function show(string $slug)
    {
        $producto = TiendaProducto::with(['medicamento.categoria', 'sucursal', 'imagenesVisibles'])
            ->where('visible', true)
            ->where('slug', $slug)
            ->firstOrFail();

        $stockDisponible = $producto->stockDisponible();

        $medicamento = $producto->medicamento;

        $relacionados = TiendaProducto::with(['medicamento.categoria', 'sucursal', 'imagenesVisibles'])
            ->where('visible', true)
            ->where('id', '!=', $producto->id)
            ->where(function ($query) use ($medicamento) {
                if ($medicamento->categoria_id) {
                    $query->whereHas('medicamento', fn($med) => $med->where('categoria_id', $medicamento->categoria_id));
                }
                if ($medicamento->laboratorio) {
                    $query->orWhereHas('medicamento', fn($med) => $med->where('laboratorio', $medicamento->laboratorio));
                }
            })
            ->get()
            ->sortBy(function ($item) use ($medicamento) {
                $mismaCat = $medicamento->categoria_id && $item->medicamento->categoria_id === $medicamento->categoria_id;
                $mismoLab = $medicamento->laboratorio && $item->medicamento->laboratorio === $medicamento->laboratorio;
                if ($mismaCat && $mismoLab) return 0;
                if ($mismaCat) return 1;
                return 2;
            })
            ->take(8);

        $idsExcluidos = $relacionados->pluck('id')->push($producto->id);

        $masProductos = TiendaProducto::with(['medicamento.categoria', 'sucursal', 'imagenesVisibles'])
            ->where('visible', true)
            ->whereNotIn('id', $idsExcluidos)
            ->inRandomOrder()
            ->take(8)
            ->get();

        return view('tienda.show', compact('producto', 'stockDisponible', 'relacionados', 'masProductos'));
    }
}
