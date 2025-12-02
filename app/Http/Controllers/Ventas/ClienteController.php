<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use App\Models\Ventas\Cliente;
use App\Repositories\ClienteRepository;
use App\Http\Requests\Ventas\StoreClienteRequest;
use App\Http\Requests\Ventas\UpdateClienteRequest;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    protected $clienteRepo;

    // Inyectamos el Repositorio en el constructor
    public function __construct(ClienteRepository $clienteRepo)
    {
        $this->clienteRepo = $clienteRepo;

        // Permisos
        $this->middleware('can:clientes.ver')->only(['index', 'search']);
        $this->middleware('can:clientes.crear')->only(['store']);
        $this->middleware('can:clientes.editar')->only(['update']);
        $this->middleware('can:clientes.eliminar')->only(['destroy']);
    }

    public function index()
    {
        $stats = $this->clienteRepo->getStats();
        $clientes = $this->clienteRepo->search([]);

        return view('ventas.clientes.index', array_merge(['clientes' => $clientes], $stats));
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

        return response()->json([
            'exists' => !!$cliente,
            'data'   => $cliente
        ]);
    }

    public function show($id)
    {
        $cliente = Cliente::with(['ventas' => function ($q) {
            $q->latest()->take(5);
        }])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $cliente
        ]);
    }

    public function store(StoreClienteRequest $request)
    {
        $cliente = Cliente::create($request->validated());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Cliente registrado correctamente.',
                'data'    => $cliente
            ]);
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
}
