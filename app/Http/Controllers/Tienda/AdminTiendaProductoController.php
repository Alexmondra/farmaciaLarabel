<?php

namespace App\Http\Controllers\Tienda;

use App\Http\Controllers\Controller;
use App\Models\Inventario\Medicamento;
use App\Models\Inventario\MedicamentoSucursal;
use App\Models\Tienda\TiendaProducto;
use App\Models\Tienda\TiendaProductoImagen;
use App\Support\WebpImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminTiendaProductoController extends Controller
{
    public function index(Request $request)
    {
        $productos = TiendaProducto::with(['medicamento', 'sucursal', 'imagenesVisibles'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = trim($request->q);
                $query->where('nombre_web', 'LIKE', "%{$term}%")
                    ->orWhereHas('medicamento', function ($medicamento) use ($term) {
                        $medicamento->where('nombre', 'LIKE', "%{$term}%")
                            ->orWhere('codigo', 'LIKE', "%{$term}%");
                    });
            })
            ->latest()
            ->paginate(20)
            ->appends($request->query());

        if ($request->ajax()) {
            return response()->json([
                'html' => view('tienda.admin.productos.partials.table', compact('productos'))->render(),
            ]);
        }

        return view('tienda.admin.productos.index', compact('productos'));
    }

    public function create()
    {
        return view('tienda.admin.productos.create');
    }

    public function buscarMedicamentos(Request $request)
    {
        $term = trim((string) $request->input('q', ''));

        if (mb_strlen($term) < 2) {
            return response()->json([]);
        }

        $medicamentos = Medicamento::query()
            ->with(['sucursales' => function ($query) {
                $query->wherePivot('activo', true)->orderBy('nombre');
            }])
            ->where('activo', true)
            ->where(function ($query) use ($term) {
                $query->where('nombre', 'LIKE', "%{$term}%")
                    ->orWhere('codigo', 'LIKE', "%{$term}%")
                    ->orWhere('codigo_barra', 'LIKE', "%{$term}%")
                    ->orWhere('codigo_barra_blister', 'LIKE', "%{$term}%");
            })
            ->limit(5)
            ->get();

        $publicados = TiendaProducto::whereIn('medicamento_id', $medicamentos->pluck('id'))
            ->get(['medicamento_id', 'sucursal_id'])
            ->map(fn($item) => $item->medicamento_id . ':' . $item->sucursal_id)
            ->all();

        return response()->json($medicamentos->map(function (Medicamento $medicamento) use ($publicados) {
            $sucursales = $medicamento->sucursales
                ->reject(fn($sucursal) => in_array($medicamento->id . ':' . $sucursal->id, $publicados, true))
                ->map(function ($sucursal) use ($medicamento) {
                    return [
                        'id' => $sucursal->id,
                        'nombre' => $sucursal->nombre,
                        'precio' => (float) $sucursal->pivot->precio_venta,
                        'stock' => (int) DB::table('lotes')
                            ->where('medicamento_id', $medicamento->id)
                            ->where('sucursal_id', $sucursal->id)
                            ->where('stock_actual', '>', 0)
                            ->sum('stock_actual'),
                    ];
                })
                ->values();

            return [
                'id' => $medicamento->id,
                'nombre' => $medicamento->nombre,
                'codigo' => $medicamento->codigo,
                'codigo_barra' => $medicamento->codigo_barra,
                'codigo_barra_blister' => $medicamento->codigo_barra_blister,
                'laboratorio' => $medicamento->laboratorio,
                'imagen_url' => $medicamento->imagen_path ? asset('storage/' . $medicamento->imagen_path) : null,
                'sucursales' => $sucursales,
            ];
        })->values());
    }

    public function store(Request $request)
    {
        $data = $this->validateProducto($request);
        [$medicamentoId, $sucursalId] = explode(':', $data['medicamento_sucursal']);

        $producto = TiendaProducto::create([
            'medicamento_id' => $medicamentoId,
            'sucursal_id' => $sucursalId,
            'slug' => $this->slugDisponible($data['nombre_web'] ?: $data['nombre_base']),
            'nombre_web' => $data['nombre_web'] ?: null,
            'descripcion_web' => $data['descripcion_web'] ?? null,
            'precio_web' => $data['precio_web'] ?? null,
            'stock_modo' => $data['stock_modo'],
            'stock_web' => $data['stock_modo'] === TiendaProducto::STOCK_MANUAL ? $data['stock_web'] : null,
            'visible' => $request->boolean('visible'),
            'destacado' => $request->boolean('destacado'),
        ]);

        $this->guardarImagenesProducto($producto, $request->file('imagenes', []));

        return redirect()->route('tienda.admin.productos.index')
            ->with('success', 'Producto publicado en tienda.');
    }

    public function edit(TiendaProducto $producto)
    {
        $producto->load(['medicamento', 'sucursal', 'imagenes']);

        return view('tienda.admin.productos.edit', compact('producto'));
    }

    public function update(Request $request, TiendaProducto $producto)
    {
        $data = $request->validate([
            'nombre_web' => ['nullable', 'string', 'max:220'],
            'descripcion_web' => ['nullable', 'string'],
            'precio_web' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'stock_modo' => ['required', Rule::in([
                TiendaProducto::STOCK_SIN_CONTROL,
                TiendaProducto::STOCK_MANUAL,
                TiendaProducto::STOCK_SUCURSAL,
            ])],
            'stock_web' => ['nullable', 'required_if:stock_modo,' . TiendaProducto::STOCK_MANUAL, 'integer', 'min:0', 'max:999999'],
            'imagenes' => ['nullable', 'array'],
            'imagenes.*' => ['image', 'mimes:jpg,jpeg,png,gif,webp', 'max:4096'],
            'imagen_alt' => ['nullable', 'array'],
            'imagen_alt.*' => ['nullable', 'string', 'max:220'],
            'imagen_orden' => ['nullable', 'array'],
            'imagen_orden.*' => ['nullable', 'integer', 'min:0', 'max:999999'],
            'imagen_visible' => ['nullable', 'array'],
        ]);

        $nombreBase = $data['nombre_web'] ?: $producto->medicamento->nombre;
        $slug = $this->slugDisponible($nombreBase, $producto->id);

        $producto->update([
            'slug' => $slug,
            'nombre_web' => $data['nombre_web'] ?? null,
            'descripcion_web' => $data['descripcion_web'] ?? null,
            'precio_web' => $data['precio_web'] ?? null,
            'stock_modo' => $data['stock_modo'],
            'stock_web' => $data['stock_modo'] === TiendaProducto::STOCK_MANUAL ? $data['stock_web'] : null,
            'visible' => $request->boolean('visible'),
            'destacado' => $request->boolean('destacado'),
        ]);

        foreach ($producto->imagenes as $imagen) {
            $imagen->update([
                'alt' => $data['imagen_alt'][$imagen->id] ?? null,
                'orden' => $data['imagen_orden'][$imagen->id] ?? 0,
                'visible' => isset($data['imagen_visible'][$imagen->id]),
            ]);
        }

        $this->guardarImagenesProducto($producto->fresh(['medicamento']), $request->file('imagenes', []));

        return redirect()->route('tienda.admin.productos.index')
            ->with('success', 'Producto de tienda actualizado.');
    }

    public function destroy(TiendaProducto $producto)
    {
        foreach ($producto->imagenes as $imagen) {
            Storage::disk('public')->delete($imagen->imagen_path);
        }

        $producto->delete();

        return redirect()->route('tienda.admin.productos.index')
            ->with('success', 'Producto retirado de la tienda.');
    }

    private function validateProducto(Request $request): array
    {
        $data = $request->validate([
            'medicamento_sucursal' => ['required', 'string'],
            'nombre_web' => ['nullable', 'string', 'max:220'],
            'descripcion_web' => ['nullable', 'string'],
            'precio_web' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'stock_modo' => ['required', Rule::in([
                TiendaProducto::STOCK_SIN_CONTROL,
                TiendaProducto::STOCK_MANUAL,
                TiendaProducto::STOCK_SUCURSAL,
            ])],
            'stock_web' => ['nullable', 'required_if:stock_modo,' . TiendaProducto::STOCK_MANUAL, 'integer', 'min:0', 'max:999999'],
            'imagenes' => ['nullable', 'array'],
            'imagenes.*' => ['image', 'mimes:jpg,jpeg,png,gif,webp', 'max:4096'],
        ]);

        abort_unless(str_contains($data['medicamento_sucursal'], ':'), 422);
        [$medicamentoId, $sucursalId] = explode(':', $data['medicamento_sucursal']);

        $existe = MedicamentoSucursal::where('medicamento_id', $medicamentoId)
            ->where('sucursal_id', $sucursalId)
            ->where('activo', true)
            ->exists();

        abort_unless($existe, 422);

        $yaPublicado = TiendaProducto::where('medicamento_id', $medicamentoId)
            ->where('sucursal_id', $sucursalId)
            ->exists();

        abort_if($yaPublicado, 422, 'Este medicamento ya esta publicado para esa sucursal.');

        $data['nombre_base'] = DB::table('medicamentos')->where('id', $medicamentoId)->value('nombre') ?: 'producto';

        return $data;
    }

    private function slugDisponible(string $nombre, ?int $ignoreId = null): string
    {
        $base = Str::slug($nombre) ?: 'producto';
        $slug = $base;
        $i = 2;

        while (TiendaProducto::where('slug', $slug)->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }

    public function destroyImagen(TiendaProducto $producto, TiendaProductoImagen $imagen)
    {
        abort_unless($imagen->tienda_producto_id === $producto->id, 404);

        Storage::disk('public')->delete($imagen->imagen_path);
        $imagen->delete();

        return redirect()->route('tienda.admin.productos.edit', $producto)
            ->with('success', 'Imagen de tienda eliminada.');
    }

    private function guardarImagenesProducto(TiendaProducto $producto, array $files): void
    {
        if (!$files) {
            return;
        }

        $producto->loadMissing('medicamento');
        $orden = (int) ($producto->imagenes()->max('orden') ?? 0);

        foreach ($files as $file) {
            if (!$producto->medicamento->imagen_path) {
                $producto->medicamento->update([
                    'imagen_path' => WebpImage::store($file, 'medicamentos'),
                ]);
                $producto->medicamento->refresh();

                continue;
            }

            $producto->imagenes()->create([
                'imagen_path' => WebpImage::store($file, 'tienda/productos'),
                'orden' => ++$orden,
                'visible' => true,
            ]);
        }
    }
}
