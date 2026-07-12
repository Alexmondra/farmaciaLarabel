<?php

namespace App\Models\Tienda;

use App\Models\Ventas\Cliente;
use Illuminate\Database\Eloquent\Model;

class Carrito extends Model
{
    protected $table = 'carrito';

    protected $fillable = [
        'cliente_id',
        'tienda_producto_id',
        'cantidad',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function producto()
    {
        return $this->belongsTo(TiendaProducto::class, 'tienda_producto_id');
    }
}
