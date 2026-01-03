<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use App\Models\Ventas\Cliente;
use App\Repositories\ClienteRepository;
use App\Http\Requests\Ventas\StoreClienteRequest;
use App\Http\Requests\Ventas\UpdateClienteRequest;
use Illuminate\Http\Request;
use App\Models\Configuracion;

class ClienteController extends Controller
{
    protected $clienteRepo;

    public function __construct(ClienteRepository $clienteRepo)
    {
        $this->clienteRepo = $clienteRepo;

        $this->middleware('can:clientes.ver')->only(['index', 'search']);
        $this->middleware('can:clientes.crear')->only(['store']);
        $this->middleware('can:clientes.editar')->only(['update']);
        $this->middleware('can:clientes.eliminar')->only(['destroy']);
    }

    public function index()
    {
        // Código limpio: El repositorio ya se encarga de esconder al ID 1
        $stats = $this->clienteRepo->getStats();
        $clientes = $this->clienteRepo->search([]);
        $config = Configuracion::first();

        return view('ventas.clientes.index', array_merge(
            [
                'clientes' => $clientes,
                'config' => $config
            ],
            $stats
        ));
    }

    public function search(Request $request)
    {
        $clientes = $this->clienteRepo->search($request->all());

        if ($request->ajax()) {
            return view('ventas.clientes.partials.table', compact('clientes'))->render();
        }

        return view('ventas.clientes.partials.table', compact('clientes'));
    }

    public function checkDocumento(Request $request)
    {
        $cliente = $this->clienteRepo->checkDocumento($request->doc, $request->except_id);
        $config = Configuracion::first();
        return response()->json([
            'exists' => !!$cliente,
            'data'   => $cliente,
            'config' => ['valor_punto' => $config->valor_punto_canje ?? 0.02]
        ]);
    }

    public function show($id)
    {
        $cliente = Cliente::with([
            'ventas' => function ($query) {
                $query->orderBy('created_at', 'desc')->limit(50);
            },
            'ventas.detalle_ventas.medicamento'
        ])->find($id);

        if (!$cliente) return response()->json(['success' => false, 'message' => 'Cliente no encontrado']);

        $config = Configuracion::first();
        return response()->json([
            'success' => true,
            'data' => $cliente,
            'config' => ['valor_punto' => $config->valor_punto_canje ?? 0.02]
        ]);
    }

    public function store(StoreClienteRequest $request)
    {
        $cliente = Cliente::create($request->validated());
        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Cliente registrado correctamente.', 'data' => $cliente]);
        }
        return redirect()->route('clientes.index');
    }

    public function update(UpdateClienteRequest $request, $id)
    {
        $cliente = Cliente::findOrFail($id);
        $cliente->update($request->validated());
        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Cliente actualizado correctamente.']);
        }
        return redirect()->route('clientes.index');
    }

    public function destroy($id)
    {
        $cliente = Cliente::findOrFail($id);
        $cliente->update(['activo' => false]);
        return response()->json(['success' => true, 'message' => 'Cliente eliminado (desactivado).']);
    }

    public function updateConfig(Request $request)
    {
        $conf = Configuracion::first();
        $conf->update([
            'puntos_por_moneda' => $request->puntos_por_moneda,
            'valor_punto_canje' => $request->valor_punto_canje,
        ]);
        return response()->json(['success' => true]);
    }
}
