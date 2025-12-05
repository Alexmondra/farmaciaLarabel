<?php

namespace App\Http\Controllers\Configuracion;

use App\Http\Controllers\Controller;
use App\Models\Configuracion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ConfiguracionController extends Controller
{
    public function index()
    {
        // Obtener la configuración (ID 1) o crearla si no existe
        $config = Configuracion::firstOrCreate(
            ['id' => 1],
            [
                'puntos_por_moneda' => 1,
                'valor_punto_canje' => 0.10,
                'sunat_produccion' => false
            ]
        );

        return view('configuracion.general.index', compact('config'));
    }

    public function update(Request $request)
    {
        $config = Configuracion::firstOrFail();

        $data = $request->validate([
            // Empresa
            'empresa_ruc'          => 'required|digits:11',
            'empresa_razon_social' => 'required|string|max:255',
            'empresa_direccion'    => 'required|string|max:255',

            // SUNAT
            'sunat_produccion'     => 'sometimes|boolean',
            'sunat_sol_user'       => 'nullable|string|max:255',
            'sunat_sol_pass'       => 'nullable|string|max:255',
            'sunat_certificado_pass' => 'nullable|string|max:255',
            'sunat_certificado_path' => 'nullable|file|mimes:pfx,p12', // Certificados digitales

            // Puntos
            'puntos_por_moneda'    => 'required|integer|min:1',
            'valor_punto_canje'    => 'required|numeric|min:0',
            'mensaje_ticket'       => 'nullable|string|max:200',
        ]);

        // Manejo del checkbox (si no viene, es false)
        $data['sunat_produccion'] = $request->has('sunat_produccion');

        // Subida del Certificado (si se envió uno nuevo)
        if ($request->hasFile('sunat_certificado_path')) {
            // Eliminar el anterior si existe
            if ($config->sunat_certificado_path) {
                Storage::delete($config->sunat_certificado_path);
            }
            // Guardar el nuevo en carpeta segura (no pública)
            $data['sunat_certificado_path'] = $request->file('sunat_certificado_path')->store('certificados');
        }

        $config->update($data);

        return redirect()->route('configuracion.general.index')
            ->with('success', 'Configuración actualizada correctamente.');
    }
}
