<?php

namespace App\Models\Ventas;

use App\Models\Sucursal;
use App\Models\User;
use App\Models\Ventas\Venta;
use Illuminate\Database\Eloquent\Model;

class CajaSesion extends Model
{
    protected $table = 'caja_sesiones';

    protected $fillable = [
        'sucursal_id',
        'user_id',
        'fecha_apertura',
        'saldo_inicial',
        'fecha_cierre',
        'saldo_teorico',
        'saldo_real',
        'diferencia',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'fecha_apertura' => 'datetime',
        'fecha_cierre'   => 'datetime',
        'saldo_inicial'  => 'decimal:2',
        'saldo_teorico'  => 'decimal:2',
        'saldo_real'     => 'decimal:2',
        'diferencia'     => 'decimal:2',
    ];

    /** Sucursal donde se abrió la caja */
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    /** Usuario (cajero) dueño de la sesión */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Ventas asociadas a esta sesión de caja */
    public function ventas()
    {
        return $this->hasMany(Venta::class, 'caja_sesion_id');
    }
}
