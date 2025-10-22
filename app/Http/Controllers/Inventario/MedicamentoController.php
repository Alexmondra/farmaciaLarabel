<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;

use App\Models\Inventario\Medicamento;
use App\Models\Inventario\Categoria;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MedicamentoController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $q = trim($request->get('q', ''));
        $sucursalFiltro = $request->get('sucursal_id');

        // Determinar sucursal activa
        $sucursalId = session('sucursal_id');
        
        // Obtener sucursales disponibles para el usuario
        $sucursalesDisponibles = $user->hasRole('Administrador') 
            ? \App\Models\Sucursal::orderBy('nombre')->get()
            : $user->sucursales()->select('sucursales.*')->orderBy('sucursales.nombre')->get();

        $medicamentos = Medicamento::with(['categoria', 'usuario', 'sucursales', 'lotes'])
            ->when($user->hasRole('Administrador'), function($query) use ($sucursalFiltro) {
                // Administrador: si hay filtro, filtrar por sucursal seleccionada
                if ($sucursalFiltro) {
                    return $query->whereHas('sucursales', function($q) use ($sucursalFiltro) {
                        $q->where('sucursal_id', $sucursalFiltro);
                    });
                }
                // Si no hay filtro, mostrar todos
                return $query;
            })
            ->when(!$user->hasRole('Administrador'), function($query) use ($sucursalId) {
                // Usuario normal: solo medicamentos de su sucursal activa
                if ($sucursalId) {
                    return $query->whereHas('sucursales', function($q) use ($sucursalId) {
                        $q->where('sucursal_id', $sucursalId);
                    });
                }
                return $query;
            })
            ->when($q, fn($query) => $query->where(fn($s) => $s
                ->where('nombre', 'like', "%$q%")
                ->orWhere('codigo', 'like', "%$q%")
                ->orWhere('laboratorio', 'like', "%$q%")))
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        // Cargar datos de medicamento_sucursal para cada medicamento
        $medicamentos->getCollection()->transform(function ($medicamento) use ($sucursalId, $sucursalFiltro) {
            $sucursalActiva = $sucursalFiltro ?? $sucursalId;
            if ($sucursalActiva) {
                $pivot = $medicamento->sucursales()->where('sucursal_id', $sucursalActiva)->first()?->pivot;
                $medicamento->pivot_data = $pivot;
                
                // Calcular stock real desde lotes
                $stockLotes = $medicamento->lotes()->where('sucursal_id', $sucursalActiva)->sum('cantidad_actual');
                $medicamento->stock_real = $stockLotes;
            }
            return $medicamento;
        });

        return view('inventario.medicamentos.index', compact('medicamentos', 'q', 'sucursalId', 'sucursalesDisponibles', 'sucursalFiltro'));
    }

    public function create()
    {
        $user = Auth::user();
        $categorias = Categoria::orderBy('nombre')->get();
        
        // Obtener sucursales disponibles para el usuario
        $sucursalesDisponibles = $user->hasRole('Administrador') 
            ? \App\Models\Sucursal::orderBy('nombre')->get()
            : $user->sucursales()->select('sucursales.*')->orderBy('sucursales.nombre')->get();
        
        // Determinar el tipo de vista según la cantidad de sucursales
        $tipoVista = $this->determinarTipoVista($sucursalesDisponibles);
        
        return view('inventario.medicamentos.create', compact('categorias', 'sucursalesDisponibles', 'tipoVista'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        
        $data = $request->validate([
            'codigo' => 'required|string|max:30|unique:medicamentos,codigo',
            'nombre' => 'required|string|max:180',
            'forma_farmaceutica' => 'nullable|string|max:100',
            'concentracion' => 'nullable|string|max:100',
            'presentacion' => 'nullable|string|max:120',
            'laboratorio' => 'nullable|string|max:120',
            'registro_sanitario' => 'nullable|string|max:60',
            'codigo_barra' => 'nullable|string|max:50',
            'descripcion' => 'nullable|string',
            'categoria_id' => 'nullable|exists:categorias,id',
            'imagen' => 'nullable|image|max:2048',
            'activo' => 'boolean',
            // Campos para sucursal específica
            'sucursal_id' => 'nullable|exists:sucursales,id',
            'precio_compra' => 'nullable|numeric|min:0',
            'precio_venta' => 'nullable|numeric|min:0',
            'stock_actual' => 'nullable|integer|min:0',
            'stock_minimo' => 'nullable|integer|min:0',
            'ubicacion' => 'nullable|string|max:100'
        ]);

        if ($request->hasFile('imagen')) {
            $data['imagen_path'] = $request->file('imagen')->store('medicamentos', 'public');
        }

        $data['user_id'] = Auth::id();
        $data['activo'] = $request->has('activo');
        
        // Crear medicamento
        $medicamento = Medicamento::create($data);

        // Si se especificó una sucursal, agregar directamente
        if ($request->filled('sucursal_id')) {
            $sucursalId = $request->sucursal_id;
            
            // Validar que el usuario tenga acceso a esta sucursal
            $sucursalesDisponibles = $user->hasRole('Administrador') 
                ? \App\Models\Sucursal::pluck('id')->toArray()
                : $user->sucursales()->pluck('sucursales.id')->toArray();
                
            if (in_array($sucursalId, $sucursalesDisponibles)) {
                $medicamento->sucursales()->attach($sucursalId, [
                    'precio_compra' => $request->precio_compra ?? 0,
                    'precio_venta' => $request->precio_venta ?? 0,
                    'stock_actual' => $request->stock_actual ?? 0,
                    'stock_minimo' => $request->stock_minimo ?? 0,
                    'ubicacion' => $request->ubicacion,
                    'updated_by' => Auth::id()
                ]);
                
                return redirect()->route('inventario.medicamentos.show', $medicamento)
                    ->with('success', 'Medicamento creado y agregado a la sucursal correctamente.');
            }
        }

        return redirect()->route('inventario.medicamentos.show', $medicamento)
            ->with('success', 'Medicamento creado correctamente. Ahora puedes agregarlo a las sucursales.');
    }

    public function show(Medicamento $medicamento)
    {
        $user = Auth::user();
        $sucursalId = session('sucursal_id');

        // Validar acceso a sucursal
        if (!$user->hasRole('Administrador') && !$medicamento->sucursales()->where('sucursal_id', $sucursalId)->exists()) {
            abort(403, 'No tienes acceso a este medicamento.');
        }

        // Obtener información de la sucursal activa
        $sucursalActiva = null;
        if ($sucursalId) {
            $sucursalActiva = \App\Models\Sucursal::find($sucursalId);
        }

        $pivot = $medicamento->sucursales()->where('sucursal_id', $sucursalId)->first()?->pivot;
        $lotes = $medicamento->lotes()->where('sucursal_id', $sucursalId)->orderBy('fecha_vencimiento')->get();

        // Obtener todas las sucursales donde está el medicamento
        $sucursalesMedicamento = $medicamento->sucursales()->get();


        return view('inventario.medicamentos.show', compact('medicamento', 'pivot', 'lotes', 'sucursalActiva', 'sucursalesMedicamento'));
    }

    public function edit(Medicamento $medicamento)
    {
        $user = Auth::user();
        $sucursalId = session('sucursal_id');

        // Validar acceso a sucursal
        if (!$user->hasRole('Administrador') && !$medicamento->sucursales()->where('sucursal_id', $sucursalId)->exists()) {
            abort(403, 'No tienes acceso a este medicamento.');
        }

        $categorias = Categoria::orderBy('nombre')->get();
        $pivot = $medicamento->sucursales()->where('sucursal_id', $sucursalId)->first()?->pivot;

        return view('inventario.medicamentos.edit', compact('medicamento', 'categorias', 'pivot'));
    }

    public function update(Request $request, Medicamento $medicamento)
    {
        $data = $request->validate([
            'codigo' => 'required|string|max:30|unique:medicamentos,codigo,' . $medicamento->id,
            'nombre' => 'required|string|max:180',
            'forma_farmaceutica' => 'nullable|string|max:100',
            'concentracion' => 'nullable|string|max:100',
            'presentacion' => 'nullable|string|max:120',
            'laboratorio' => 'nullable|string|max:120',
            'registro_sanitario' => 'nullable|string|max:60',
            'codigo_barra' => 'nullable|string|max:50',
            'descripcion' => 'nullable|string',
            'categoria_id' => 'nullable|exists:categorias,id',
            'imagen' => 'nullable|image|max:2048',
            'activo' => 'boolean'
        ]);

        if ($request->hasFile('imagen')) {
            if ($medicamento->imagen_path) Storage::disk('public')->delete($medicamento->imagen_path);
            $data['imagen_path'] = $request->file('imagen')->store('medicamentos', 'public');
        }

        $data['activo'] = $request->has('activo');

        // Actualizar solo información general del medicamento
        $medicamento->update($data);

        return redirect()->route('inventario.medicamentos.show', $medicamento)
            ->with('success', 'Medicamento actualizado correctamente.');
    }

    public function destroy(Medicamento $medicamento)
    {
        if ($medicamento->imagen_path) {
            Storage::disk('public')->delete($medicamento->imagen_path);
        }
        $medicamento->delete();
        return back()->with('success', 'Medicamento eliminado.');
    }

    /**
     * Determina el tipo de vista según las sucursales disponibles
     */
    private function determinarTipoVista($sucursalesDisponibles)
    {
        $cantidadSucursales = $sucursalesDisponibles->count();
        
        if ($cantidadSucursales === 0) {
            return 'sin_sucursales';
        } elseif ($cantidadSucursales === 1) {
            return 'sucursal_unica';
        } else {
            return 'multiples_sucursales';
        }
    }
}
