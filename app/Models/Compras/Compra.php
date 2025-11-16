<?php

namespace App\Models\Compras;

use App\Models\Sucursal;

use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    protected $table = 'compras';

    protected $fillable = [
        'sucursal_id',
        'proveedor_id',
        'user_id',
        'numero_factura_proveedor',
        'fecha_recepcion',
        'costo_total_factura',
        'observaciones',
        'estado',
        'tipo_comprobante',        // 👈 nuevo
        'archivo_comprobante',     // 👈 nuevo
    ];

    protected $casts = [
        'fecha_recepcion'     => 'date',
        'costo_total_factura' => 'decimal:2',
    ];

    /* -----------------------------
       RELACIONES
    ------------------------------*/

    // 1 compra pertenece a un proveedor
    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }

    // 1 compra pertenece a una sucursal
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    // 1 compra tiene muchos detalles
    public function detalles()
    {
        return $this->hasMany(DetalleCompra::class);
    }
}
