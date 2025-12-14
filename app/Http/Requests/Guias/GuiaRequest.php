<?php

namespace App\Http\Requests\Guias;

use Illuminate\Foundation\Http\FormRequest;

class GuiaRequest extends FormRequest
{
    public function authorize()
    {
        return true; // La seguridad la maneja el middleware/rutas
    }

    public function rules()
    {
        switch ($this->method()) {

            case 'POST':
                return $this->rulesForCreate();

            case 'PUT':
            case 'PATCH':
                if ($this->routeIs('guias.recibir')) {
                    return $this->rulesForRecibir();
                }
                if ($this->routeIs('guias.anular')) {
                    return ['motivo_anulacion' => 'required|string|min:5|max:255'];
                }
                return [];

            case 'DELETE':
                return [
                    'password_confirm' => 'required|current_password'
                ];

            default:
                return [];
        }
    }

    protected function rulesForCreate()
    {
        return [
            // GENERALES
            'serie'              => 'required|string',
            'numero'             => 'required|integer',
            'fecha_traslado'     => 'required|date',
            'motivo_traslado'    => 'required|string',
            'modalidad_traslado' => 'required|string|in:01,02',
            'peso_bruto'         => 'required|numeric|min:0.001',
            'items'              => 'required|json',
            'venta_id'           => 'nullable|integer|exists:ventas,id',
            'cliente_id'         => 'nullable|integer',

            // DESTINO
            'direccion_llegada'  => 'required|string|max:200',
            'ubigeo_llegada'     => 'required|string|size:6',

            // CONDICIONAL TRANSPORTE PÚBLICO
            'doc_transportista_numero'   => 'required_if:modalidad_traslado,01|nullable|digits:11',
            'razon_social_transportista' => 'required_if:modalidad_traslado,01|nullable|string|max:150',

            // CONDICIONAL TRANSPORTE PRIVADO
            'placa_vehiculo'    => 'required_if:modalidad_traslado,02|nullable|string|max:10',
            'doc_chofer_numero' => 'required_if:modalidad_traslado,02|nullable|digits:8',
            'nombre_chofer'     => 'required_if:modalidad_traslado,02|nullable|string|max:150',
            'licencia_conducir' => 'required_if:modalidad_traslado,02|nullable|string|max:20',
        ];
    }

    /**
     * Reglas para cuando la otra sucursal recibe la guía
     */
    protected function rulesForRecibir()
    {
        return [
            'fecha_recepcion' => 'required|date|after_or_equal:fecha_traslado',
            'observaciones'   => 'nullable|string|max:500',
            'conformidad'     => 'required|boolean', // 1=Todo bien, 0=Hubo problemas
        ];
    }

    public function messages()
    {
        return [
            'doc_transportista_numero.required_if' => 'El RUC es obligatorio para transporte público.',
            'placa_vehiculo.required_if'           => 'La placa es obligatoria para transporte privado.',
            'fecha_recepcion.after_or_equal'       => 'La recepción no puede ser antes del traslado.',
        ];
    }
}
