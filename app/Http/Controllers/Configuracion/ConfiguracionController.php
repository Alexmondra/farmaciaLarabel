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
        // Obtener configuración (ID 1) o crearla por defecto
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

            // AHORA ACEPTAMOS TAMBIÉN .PEM y .TXT
            'sunat_certificado_path' => 'nullable|file|mimes:pfx,p12,pem,txt',

            // Puntos
            'puntos_por_moneda'    => 'required|integer|min:1',
            'valor_punto_canje'    => 'required|numeric|min:0',
            'mensaje_ticket'       => 'nullable|string|max:200',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
        ]);

        // Manejo del checkbox (si no viene checkeado, es false)
        $data['sunat_produccion'] = $request->has('sunat_produccion');

        if ($request->hasFile('logo')) {
            // Borrar logo anterior si existe y no es el default (opcional)
            if ($config->ruta_logo && Storage::disk('public')->exists($config->ruta_logo)) {
                Storage::disk('public')->delete($config->ruta_logo);
            }

            // Guardar nuevo en carpeta 'public/logos'
            $path = $request->file('logo')->store('logos', 'public');
            $data['ruta_logo'] = $path;
        }
        // --- LÓGICA DE CERTIFICADO INTELIGENTE ---
        if ($request->hasFile('sunat_certificado_path')) {

            $file = $request->file('sunat_certificado_path');
            $extension = strtolower($file->getClientOriginalExtension());
            $content = file_get_contents($file->getRealPath());
            $finalPemContent = $content; // Por defecto asumimos que ya es PEM

            // Si el usuario subió un PFX/P12, intentamos convertirlo a PEM
            if (in_array($extension, ['pfx', 'p12'])) {
                $password = $request->input('sunat_certificado_pass');

                $certs = [];
                // Intentamos leer el PFX con la contraseña
                if (openssl_pkcs12_read($content, $certs, $password)) {
                    // Extraemos Clave Privada + Certificado Público
                    $finalPemContent = $certs['pkey'] . $certs['cert'];
                } else {
                    return back()->withErrors(['sunat_certificado_pass' => 'La contraseña del certificado PFX es incorrecta o el archivo está dañado.']);
                }
            }

            if ($config->sunat_certificado_path && Storage::exists($config->sunat_certificado_path)) {
                Storage::delete($config->sunat_certificado_path);
            }


            $nombreArchivo = 'certificados/' . $request->empresa_ruc . '_produccion.pem';
            Storage::put($nombreArchivo, $finalPemContent);

            $data['sunat_certificado_path'] = $nombreArchivo;
        }

        $config->update($data);

        return redirect()->route('configuracion.general.index')
            ->with('success', 'Configuración actualizada. ¡Listo para facturar!');
    }
}
