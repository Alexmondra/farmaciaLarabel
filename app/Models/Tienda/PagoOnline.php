<?php

namespace App\Models\Tienda;

use Illuminate\Database\Eloquent\Model;

class PagoOnline extends Model
{
    protected $table = 'pagos_online';

    protected $fillable = [
        'pedido_online_id',
        'proveedor',
        'referencia_externa',
        'estado',
        'monto',
        'moneda',
        'payload',
        'pagado_at',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'payload' => 'array',
        'pagado_at' => 'datetime',
    ];

    public function pedido()
    {
        return $this->belongsTo(PedidoOnline::class, 'pedido_online_id');
    }
}
