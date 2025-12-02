<?php

namespace App\Http\Requests\Ventas;

use Illuminate\Foundation\Http\FormRequest;

class StoreClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Asumimos que el middleware del controlador maneja los permisos
    }

    public function rules(): array
    {
        return [
            'tipo_documento' => 'required|string|max:10',
            'documento'      => 'required|unique:clientes,documento|max:20',
            'nombre'         => 'required_if:tipo_documento,DNI|nullable|string|max:255',
            'apellidos'      => 'required_if:tipo_documento,DNI|nullable|string|max:255',
            'razon_social'   => 'required_if:tipo_documento,RUC|nullable|string|max:255',
            'email'          => 'nullable|email|max:100',
            'telefono'       => 'nullable|string|max:30',
            'direccion'      => 'nullable|string|max:255',
            'sexo'           => 'nullable|in:M,F',
        ];
    }

    public function messages(): array
    {
        return [
            'documento.unique' => 'El número de documento ya está registrado.',
            'nombre.required_if' => 'El nombre es obligatorio para DNI.',
            'razon_social.required_if' => 'La razón social es obligatoria para RUC.',
        ];
    }
}
