<?php

namespace App\Models\Guias;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\Ventas\Venta;    // Ajusta si tu namespace es diferente
use App\Models\Ventas\Cliente;  // Ajusta si tu namespace es diferente

class GuiaRemision extends Model
{
    use HasFactory;

    protected $table = 'guias_remision';

    protected $fillable = [
        // RELACIONES
        'sucursal_id',
        'venta_id',
        'cliente_id',
        'user_id',

        // IDENTIFICACIÓN
        'serie',
        'numero',
        'fecha_emision',
        'fecha_traslado',

        // DATOS DEL TRASLADO
        'motivo_traslado',
        'descripcion_motivo',
        'modalidad_traslado',
        'peso_bruto',
        'unidad_medida',
        'numero_bultos',

        // PARTIDA
        'ubigeo_partida',
        'direccion_partida',
        'codigo_establecimiento_partida',

        // LLEGADA
        'ubigeo_llegada',
        'direccion_llegada',
        'codigo_establecimiento_llegada',

        // TRANSPORTE PRIVADO
        'doc_chofer_tipo',
        'doc_chofer_numero',
        'nombre_chofer',
        'licencia_conducir',
        'placa_vehiculo',

        // TRANSPORTE PÚBLICO
        'doc_transportista_tipo',
        'doc_transportista_numero',
        'razon_social_transportista',

        // ESTADOS Y SUNAT
        'estado_traslado',
        'ruta_xml',
        'ruta_cdr',
        'ruta_pdf',
        'hash',
        'ticket_sunat',
        'sunat_exito',
        'mensaje_sunat',
        'codigo_error_sunat'
    ];

    protected $casts = [
        'fecha_emision'  => 'datetime',
        'fecha_traslado' => 'date',
        'peso_bruto'     => 'decimal:3',
        'sunat_exito'    => 'boolean',
    ];

    // --- RELACIONES ---

    /** La sucursal desde donde sale la guía */
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    /** (Opcional) La venta que originó la guía */
    public function venta()
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    /** (Opcional) El destinatario */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    /** El usuario que creó la guía */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Los productos que lleva la guía */
    public function detalles()
    {
        return $this->hasMany(DetalleGuiaRemision::class, 'guia_remision_id');
    }

    public function getEstadoVisualAttribute()
    {
        // 1. Si está ANULADO o ENTREGADO, respetamos ese estado final.
        if (in_array($this->estado_traslado, ['ANULADO', 'ENTREGADO'])) {
            return $this->estado_traslado;
        }

        // 2. Si está REGISTRADO, verificamos la fecha.
        if ($this->estado_traslado === 'REGISTRADO') {
            $hoy = now()->format('Y-m-d');

            // Si la fecha de traslado ya llegó o pasó, visualmente es EN TRANSITO
            if ($this->fecha_traslado && $this->fecha_traslado->format('Y-m-d') <= $hoy) {
                return 'EN TRANSITO';
            }
        }

        // 3. Si no cumple nada anterior, devolvemos el estado real de la BD
        return $this->estado_traslado;
    }

    public function getColorEstadoAttribute()
    {
        return match ($this->estado_visual) {
            'REGISTRADO'  => 'secondary', // Gris
            'EN TRANSITO' => 'primary',   // Azul
            'ENTREGADO'   => 'success',   // Verde
            'ANULADO'     => 'danger',    // Rojo
            default       => 'dark'
        };
    }
}
