<?php

namespace App\Http\Requests\Guias;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\UbigeoExiste;
use App\Models\Guias\GuiaRemision;

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
        // El motivo '04' (Traslado entre establecimientos) no requiere cliente.
        $requiereCliente = $this->input('motivo_traslado') !== '04' ? 'required' : 'nullable';

        return [
            // ===============================================
            // 1. GENERALES
            // ===============================================
            'serie'              => 'required|string|max:4',
            'numero'             => 'required|integer|min:1',
            'fecha_traslado'     => 'required|date|after_or_equal:today',
            'motivo_traslado'    => 'required|string|in:01,02,04,08,09,13,14', // Códigos SUNAT
            'descripcion_motivo' => 'nullable|string|max:200', // Campo descriptivo
            'modalidad_traslado' => 'required|string|in:01,02',
            'peso_bruto'         => 'required|numeric|min:0.001',
            'numero_bultos'      => 'nullable|integer|min:1',
            'venta_id'           => 'nullable|integer|exists:ventas,id',

            // CLIENTE: CONDICIONAL
            'cliente_id'         => [$requiereCliente, 'integer', 'exists:clientes,id'],

            // ===============================================
            // 2. DESTINO (UBIGEOS)
            // ===============================================
            'ubigeo_partida'     => ['nullable', 'string', new UbigeoExiste], // Si es editable, debe validarse
            'codigo_establecimiento_partida' => 'nullable|string|max:4',
            'direccion_partida'  => 'nullable|string|max:200',
            'direccion_llegada'  => 'required|string|max:200',
            'ubigeo_llegada'     => ['required', 'string', new UbigeoExiste], // ¡VALIDACIÓN CRÍTICA!
            'codigo_establecimiento_llegada' => 'required_if:motivo_traslado,04|nullable|string|max:4',
            // ===============================================
            // 3. ITEMS (Detalles de la Guía)
            // ===============================================
            'items'                 => 'required|json', // Se valida el contenido en el foreach (ver after())

            // ===============================================
            // 4. TRANSPORTE
            // ===============================================
            // TRASLADO: PÚBLICO (modalidad 01)
            'doc_transportista_numero'   => 'required_if:modalidad_traslado,01|nullable|digits:11', // RUC del Transportista
            'razon_social_transportista' => 'required_if:modalidad_traslado,01|nullable|string|max:150',

            // TRASLADO: PRIVADO (modalidad 02)
            'placa_vehiculo'    => 'required_if:modalidad_traslado,02|nullable|string|max:10',
            'doc_chofer_numero' => 'required_if:modalidad_traslado,02|nullable|digits_between:8,12', // DNI/CE del Chofer
            'nombre_chofer'     => 'nullable|string|max:150',
            'licencia_conducir' => 'nullable|string|max:20',
        ];
    }

    /**
     * Reglas para cuando la otra sucursal recibe la guía
     */
    protected function rulesForRecibir()
    {
        $guia = GuiaRemision::findOrFail($this->route('guia'));
        $fechaTraslado = $guia->fecha_traslado->format('Y-m-d');

        return [
            'fecha_recepcion' => ['required', 'date', 'after_or_equal:' . $fechaTraslado],
            'observaciones'   => 'nullable|string|max:500',
            'conformidad'     => 'required|boolean',
        ];
    }


    public function messages()
    {
        return [
            'ubigeo_llegada.required'              => 'El Ubigeo de llegada es obligatorio.',
            'ubigeo_llegada.size'                  => 'El Ubigeo debe tener exactamente 6 dígitos.',
            'doc_transportista_numero.required_if' => 'El RUC del transportista es obligatorio para transporte público.',
            'placa_vehiculo.required_if'           => 'La placa del vehículo es obligatoria para transporte privado.',
            'doc_chofer_numero.required_if'        => 'El DNI/CE del chófer es obligatorio para transporte privado.',
            'cliente_id.required'                  => 'Debe seleccionar el cliente destinatario, salvo que sea Traslado Interno (Motivo 04).',
            'fecha_traslado.after_or_equal'        => 'La fecha de traslado no puede ser anterior al día de hoy.',
            'fecha_recepcion.after_or_equal'       => 'La recepción no puede ser antes de la fecha de traslado.',
        ];
    }
}
