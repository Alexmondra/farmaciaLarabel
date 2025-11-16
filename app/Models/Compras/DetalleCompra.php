<?php

namespace App\Models\Compras;


use Illuminate\Database\Eloquent\Model;
use App\Models\Compras\Compra;
use App\Models\Inventario\Lote;



class DetalleCompra extends Model
{
    protected $table = 'detalle_compras';

    protected $fillable = [
        'compra_id',
        'lote_id',
        'cantidad_recibida',
        'precio_compra_unitario',
    ];

    protected $casts = [
        'cantidad_recibida'          => 'integer',
        'precio_compra_unitario' => 'decimal:4',
    ];

    /* -----------------------------
       RELACIONES
    ------------------------------*/

    // Cada detalle pertenece a una compra
    public function compra()
    {
        return $this->belongsTo(Compra::class);
    }

    public function lote()
    {
        return $this->belongsTo(Lote::class);
    }
}
