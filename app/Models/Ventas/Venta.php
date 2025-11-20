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
        'caja_sesion_id',
        'sucursal_id',
        'cliente_id',
        'user_id',
        'tipo_comprobante',
        'serie',
        'numero',
        'fecha_emision',
        'total_bruto',
        'total_descuento',
        'total_neto',
        'medio_pago',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'fecha_emision'  => 'datetime',
        'total_bruto'    => 'decimal:2',
        'total_descuento' => 'decimal:2',
        'total_neto'     => 'decimal:2',
    ];

    /** Sesión de caja donde se registró esta venta */
    public function cajaSesion()
    {
        return $this->belongsTo(CajaSesion::class, 'caja_sesion_id');
    }

    /** Sucursal de la venta (duplicado a propósito, para reportes) */
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    /** Cliente que realizó la compra */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    /** Usuario (vendedor / cajero) que hizo la venta */
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
