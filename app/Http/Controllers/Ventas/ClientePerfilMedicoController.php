<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use App\Models\Ventas\Cliente;
use App\Models\Tienda\ChatPerfilMedico;
use Illuminate\Http\Request;

class ClientePerfilMedicoController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:clientes.ver')->only(['index']);
        $this->middleware('can:clientes.editar')->only(['store', 'update', 'destroy']);
    }

    /**
     * Listar todos los perfiles médicos de un cliente.
     */
    public function index($clienteId)
    {
        $cliente = Cliente::findOrFail($clienteId);
        $perfiles = ChatPerfilMedico::where('cliente_id', $clienteId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $perfiles,
            'cliente' => [
                'id' => $cliente->id,
                'nombre_completo' => $cliente->nombre_completo
            ]
        ]);
    }

    /**
     * Crear un nuevo perfil médico para un cliente.
     */
    public function store(Request $request, $clienteId)
    {
        $request->validate([
            'antecedentes' => 'required|string',
            'device_fingerprint' => 'nullable|string|max:255',
        ]);

        $cliente = Cliente::findOrFail($clienteId);

        $perfil = ChatPerfilMedico::create([
            'cliente_id' => $cliente->id,
            'device_fingerprint' => $request->device_fingerprint,
            'antecedentes' => $request->antecedentes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Perfil médico creado correctamente.',
            'data' => $perfil
        ]);
    }

    /**
     * Actualizar un perfil médico existente.
     */
    public function update(Request $request, $perfilId)
    {
        $request->validate([
            'antecedentes' => 'required|string',
            'device_fingerprint' => 'nullable|string|max:255',
        ]);

        $perfil = ChatPerfilMedico::findOrFail($perfilId);
        $perfil->update([
            'device_fingerprint' => $request->device_fingerprint,
            'antecedentes' => $request->antecedentes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Perfil médico actualizado correctamente.',
            'data' => $perfil
        ]);
    }

    /**
     * Eliminar un perfil médico.
     */
    public function destroy($perfilId)
    {
        $perfil = ChatPerfilMedico::findOrFail($perfilId);
        $perfil->delete();

        return response()->json([
            'success' => true,
            'message' => 'Perfil médico eliminado correctamente.'
        ]);
    }
}
