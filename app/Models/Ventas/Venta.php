<?php

namespace App\Models\Ventas;

use App\Models\Ventas\CajaSesion;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\Ventas\DetalleVenta;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    protected $table = 'ventas';

    protected $fillable = [
        // Relaciones
        'caja_sesion_id',
        'sucursal_id',
        'cliente_id',
        'user_id',

        // Datos Comprobante
        'tipo_comprobante',
        'serie',
        'numero',
        'fecha_emision',

        // Totales Económicos
        'total_bruto',
        'total_descuento',
        'total_neto',

        // --- NUEVO: CAMPOS SUNAT (IMPUESTOS) ---
        'op_gravada',
        'op_exonerada',
        'op_inafecta',
        'total_igv',
        'porcentaje_igv',

        // --- NUEVO: RESPUESTA FACTURACIÓN ELECTRÓNICA ---
        'ruta_xml',
        'ruta_cdr',
        'ruta_pdf',
        'codigo_error_sunat',
        'mensaje_sunat',
        'hash',

        // Otros
        'medio_pago',
        'monto_recibido',
        'referencia_pago',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'fecha_emision'   => 'datetime',
        'total_bruto'     => 'decimal:2',
        'total_descuento' => 'decimal:2',
        'total_neto'      => 'decimal:2',

        // Casts para los nuevos campos
        'op_gravada'      => 'decimal:2',
        'op_exonerada'    => 'decimal:2',
        'op_inafecta'     => 'decimal:2',
        'total_igv'       => 'decimal:2',
        'porcentaje_igv'  => 'decimal:2',
    ];

    /** Sesión de caja donde se registró esta venta */
    public function cajaSesion()
    {
        return $this->belongsTo(CajaSesion::class, 'caja_sesion_id');
    }

    /** Sucursal de la venta */
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    /** Cliente que realizó la compra */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    /** Usuario (vendedor / cajero) */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Detalles (productos) de la venta */
    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class, 'venta_id');
    }
}
