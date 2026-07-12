<?php

namespace App\Models\Tienda;

use App\Models\Inventario\Lote;
use App\Models\Inventario\Medicamento;
use Illuminate\Database\Eloquent\Model;

class PedidoOnlineDetalle extends Model
{
    protected $table = 'pedido_online_detalles';

    protected $fillable = [
        'pedido_online_id',
        'medicamento_id',
        'lote_id',
        'descripcion',
        'cantidad',
        'precio_unitario',
        'subtotal',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function pedido()
    {
        return $this->belongsTo(PedidoOnline::class, 'pedido_online_id');
    }

    public function medicamento()
    {
        return $this->belongsTo(Medicamento::class);
    }

    public function lote()
    {
        return $this->belongsTo(Lote::class);
    }
}
