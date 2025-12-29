<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SucursalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Permitir a todos los usuarios autenticados
    }

    public function rules(): array
    {
        // Obtenemos el ID de la sucursal si estamos editando (desde la ruta)
        // Si la ruta es 'sucursales/{sucursal}', obtenemos ese objeto.
        $sucursal = $this->route('sucursal');
        $id = $sucursal ? $sucursal->id : null;

        return [
            // --- VALIDACIÓN CÓDIGO SUNAT ---
            // Unique: Ignora el ID actual si estamos editando
            'codigo' => [
                'required',
                'string',
                'size:4',
                Rule::unique('sucursales')->ignore($id)
            ],

            'nombre'              => ['required', 'string', 'max:120'],
            'ubigeo'              => ['nullable', 'string', 'max:6'],
            'departamento'        => ['nullable', 'string', 'max:100'],
            'provincia'           => ['nullable', 'string', 'max:100'],
            'distrito'            => ['nullable', 'string', 'max:100'],
            'direccion'           => ['nullable', 'string', 'max:200'],
            'telefono'            => ['nullable', 'string', 'max:30'],
            'email'               => ['nullable', 'email', 'max:120'],
            'impuesto_porcentaje' => ['required', 'numeric', 'min:0', 'max:100'],
            'imagen_sucursal'     => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'serie_boleta'        => ['required', 'string', 'max:4', Rule::unique('sucursales')->ignore($id)],
            'serie_factura'       => ['required', 'string', 'max:4', Rule::unique('sucursales')->ignore($id)],
            'serie_ticket'        => ['required', 'string', 'max:4', Rule::unique('sucursales')->ignore($id)],
            'serie_nc_boleta'     => ['required', 'string', 'max:4', Rule::unique('sucursales')->ignore($id)],
            'serie_nc_factura'    => ['required', 'string', 'max:4', Rule::unique('sucursales')->ignore($id)],
            'serie_guia'          => ['required', 'string', 'max:4', Rule::unique('sucursales')->ignore($id)],
            'cod_establecimiento_digemid' => 'nullable|string|max:50',
            'activo'              => ['sometimes', 'boolean'],
        ];
    }

    // Opcional: Preparar datos antes de validar (ej: convertir booleano)
    protected function prepareForValidation()
    {
        $this->merge([
            'activo' => $this->boolean('activo'),
        ]);
    }
}
