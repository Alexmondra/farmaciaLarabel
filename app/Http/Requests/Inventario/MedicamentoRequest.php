<?php

namespace App\Http\Requests\Inventario;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MedicamentoRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $parametro = $this->route('id')
            ?? $this->route('medicamento')
            ?? $this->input('id');

        $id = is_object($parametro) ? $parametro->id : $parametro;

        $reglaUnicidadCompuesta = Rule::unique('medicamentos', 'nombre')
            ->where(function ($query) {
                return $query->where('laboratorio', $this->laboratorio)
                    ->where('concentracion', $this->concentracion);
            })
            ->ignore($id);

        // 3. REGLAS
        return [
            // APLICAMOS LA REGLA COMPUESTA AQUÍ
            'nombre' => [
                'required',
                'string',
                'max:180',
                $reglaUnicidadCompuesta // <--- AQUÍ ESTÁ LA MAGIA
            ],

            // Resto de validaciones normales
            'codigo' => [
                'required',
                'string',
                'max:30',
                Rule::unique('medicamentos', 'codigo')->ignore($id)
            ],
            'codigo_barra' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('medicamentos', 'codigo_barra')->ignore($id)
            ],
            'codigo_barra_blister' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('medicamentos', 'codigo_barra_blister')->ignore($id)
            ],

            'unidades_por_envase'  => 'required|integer|min:1',
            'codigo_digemid'       => 'nullable|string|max:50',
            'laboratorio'          => 'nullable|string|max:120',
            'categoria_id'         => 'nullable|exists:categorias,id',
            'forma_farmaceutica'   => 'nullable|string|max:100',
            'presentacion'         => 'nullable|string|max:120',
            'concentracion'        => 'nullable|string|max:100',
            'descripcion'          => 'nullable|string',
            'registro_sanitario'   => 'nullable|string|max:60',
            'unidades_por_blister' => 'nullable|integer|min:1',

            'afecto_igv'           => 'sometimes',
            'receta_medica'        => 'sometimes',
            'imagen'               => 'nullable|image|max:2048',
        ];
    }

    public function messages()
    {
        return [
            'nombre.unique' => 'Ya existe un medicamento con este Nombre, Laboratorio y Concentración idénticos.',
            'codigo.unique' => 'El Código Interno ya está en uso.',
            'codigo_barra.unique' => 'El Código de Barra ya está registrado.',
        ];
    }
}
