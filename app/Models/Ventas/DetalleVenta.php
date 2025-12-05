<?php

namespace App\Models\Ventas;

use App\Models\Inventario\Lote;
use App\Models\Inventario\Medicamento;
use Illuminate\Database\Eloquent\Model;

class DetalleVenta extends Model
{
    protected $table = 'detalle_ventas';

    protected $fillable = [
        'venta_id',
        'lote_id',
        'medicamento_id',
        'cantidad',

        // Precios Cliente
        'precio_unitario',
        'descuento_unitario',

        // --- NUEVO: DATOS INTERNOS SUNAT ---
        'valor_unitario',   // Precio BASE (Sin impuestos)
        'igv',              // Monto del impuesto
        'tipo_afectacion',  // Código '10', '20', etc.

        // Subtotales
        'subtotal_bruto',
        'subtotal_descuento',
        'subtotal_neto',
    ];

    protected $casts = [
        'cantidad'           => 'integer',
        'precio_unitario'    => 'decimal:4', // A veces conviene 4 decimales para precisión
        'descuento_unitario' => 'decimal:4',
        'valor_unitario'     => 'decimal:4', // Nuevo
        'igv'                => 'decimal:4', // Nuevo

        'subtotal_bruto'     => 'decimal:2',
        'subtotal_descuento' => 'decimal:2',
        'subtotal_neto'      => 'decimal:2',
    ];

    /** Venta a la que pertenece este detalle */
    public function venta()
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    /** Lote desde el que se descontó el stock */
    public function lote()
    {
        return $this->belongsTo(Lote::class, 'lote_id');
    }

    /** Medicamento vendido */
    public function medicamento()
    {
        return $this->belongsTo(Medicamento::class, 'medicamento_id');
    }
}
