<?php

namespace App\Http\Requests\Ventas;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Obtenemos el ID del cliente de la ruta para ignorarlo en la validación unique
        $clienteId = $this->route('cliente');

        return [
            'tipo_documento' => 'required|string|max:10',
            'documento'      => 'required|max:20|unique:clientes,documento,' . $clienteId,
            'nombre'         => 'required_if:tipo_documento,DNI|nullable|string|max:255',
            'apellidos'      => 'required_if:tipo_documento,DNI|nullable|string|max:255',
            'razon_social'   => 'required_if:tipo_documento,RUC|nullable|string|max:255',
            'email'          => 'nullable|email|max:100',
            'telefono'       => 'nullable|string|max:30',
            'direccion'      => 'nullable|string|max:255',
            'sexo'           => 'nullable|in:M,F',
        ];
    }
}
