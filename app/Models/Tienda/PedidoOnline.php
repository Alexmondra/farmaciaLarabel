<?php

namespace App\Models\Tienda;

use App\Models\Sucursal;
use App\Models\Ventas\Cliente;
use App\Models\Ventas\Venta;
use Illuminate\Database\Eloquent\Model;

class PedidoOnline extends Model
{
    protected $table = 'pedidos_online';

    protected $fillable = [
        'codigo',
        'qr_token',
        'cliente_id',
        'sucursal_id',
        'venta_id',
        'cliente_tipo_documento',
        'cliente_documento',
        'cliente_nombre',
        'cliente_telefono',
        'cliente_email',
        'direccion_entrega',
        'fecha_recojo',
        'tipo_entrega',
        'metodo_pago',
        'estado_pago',
        'estado',
        'subtotal',
        'costo_envio',
        'total',
        'observaciones',
        'confirmado_at',
        'entregado_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'costo_envio' => 'decimal:2',
        'total' => 'decimal:2',
        'fecha_recojo' => 'datetime',
        'confirmado_at' => 'datetime',
        'entregado_at' => 'datetime',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function detalles()
    {
        return $this->hasMany(PedidoOnlineDetalle::class);
    }

    public function pagos()
    {
        return $this->hasMany(PagoOnline::class);
    }
}
