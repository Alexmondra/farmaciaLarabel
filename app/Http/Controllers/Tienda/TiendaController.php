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
    public function sucursales()
    {
        $sucursales = Sucursal::where('activo', true)->orderBy('nombre')->get();
        $sucursalesJson = $sucursales->map(function($s) {
            return [
                'id' => $s->id,
                'nombre' => $s->nombre,
                'direccion' => $s->direccion ?? 'Ubicación principal',
                'distrito' => $s->distrito,
                'provincia' => $s->provincia,
                'latitud' => $s->latitud ? (float)$s->latitud : null,
                'longitud' => $s->longitud ? (float)$s->longitud : null,
                'url_catalogo' => route('tienda.index', ['sucursal' => $s->id])
            ];
        });
        return view('tienda.sucursales', compact('sucursales', 'sucursalesJson'));
    }

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
            ->select('tienda_productos.*')
            ->leftJoin('medicamento_sucursal', function ($join) {
                $join->on('medicamento_sucursal.medicamento_id', '=', 'tienda_productos.medicamento_id')
                     ->on('medicamento_sucursal.sucursal_id', '=', 'tienda_productos.sucursal_id');
            })
            ->addSelect('medicamento_sucursal.precio_venta as precio_sucursal')
            ->where('tienda_productos.visible', true)
            ->when($request->filled('sucursal'), function ($query) use ($request) {
                $query->where('tienda_productos.sucursal_id', $request->integer('sucursal'));
            })
            ->when($request->filled('categoria'), function ($query) use ($request) {
                $query->whereHas('medicamento', function ($medicamento) use ($request) {
                    $medicamento->where('categoria_id', $request->integer('categoria'));
                });
            })
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = trim($request->q);
                $query->where(function ($sub) use ($term) {
                    $sub->where('tienda_productos.nombre_web', 'LIKE', "%{$term}%")
                        ->orWhereHas('medicamento', function ($medicamento) use ($term) {
                            $medicamento->where('nombre', 'LIKE', "%{$term}%")
                                ->orWhere('laboratorio', 'LIKE', "%{$term}%")
                                ->orWhere('codigo', 'LIKE', "%{$term}%");
                        });
                });
            })
            ->when($request->filled('precio_max'), function ($query) use ($request) {
                $max = (float) $request->precio_max;
                $query->where(function ($sub) use ($max) {
                    $sub->whereRaw('COALESCE(tienda_productos.precio_web, medicamento_sucursal.precio_venta) <= ?', [$max]);
                });
            })
            ->orderByDesc('tienda_productos.destacado')
            ->orderByDesc('tienda_productos.id')
            ->orderBy('tienda_productos.nombre_web')
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

    public function sugerencias(Request $request)
    {
        $term = trim($request->q);
        if (strlen($term) < 2) {
            return response()->json([]);
        }

        $productos = TiendaProducto::with(['imagenesVisibles', 'sucursal', 'medicamento'])
            ->select('tienda_productos.*')
            ->leftJoin('medicamento_sucursal', function ($join) {
                $join->on('medicamento_sucursal.medicamento_id', '=', 'tienda_productos.medicamento_id')
                     ->on('medicamento_sucursal.sucursal_id', '=', 'tienda_productos.sucursal_id');
            })
            ->addSelect('medicamento_sucursal.precio_venta as precio_sucursal')
            ->where('tienda_productos.visible', true)
            ->where(function ($query) use ($term) {
                $query->where('tienda_productos.nombre_web', 'LIKE', "%{$term}%")
                      ->orWhereHas('medicamento', function ($med) use ($term) {
                          $med->where('nombre', 'LIKE', "%{$term}%")
                              ->orWhere('laboratorio', 'LIKE', "%{$term}%");
                      });
            })
            ->take(5)
            ->get();

        $sugerencias = $productos->map(function ($prod) {
            return [
                'id' => $prod->id,
                'nombre' => $prod->nombre,
                'precio' => number_format($prod->precioVenta(), 2),
                'imagen_url' => $prod->imagen_url ?: '/vendor/adminlte/dist/img/avatar.png',
                'url' => route('tienda.productos.show', $prod->slug),
                'sucursal' => $prod->sucursal->nombre ?? 'General',
                'laboratorio' => $prod->medicamento->laboratorio ?? '',
            ];
        });

        return response()->json($sugerencias);
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
