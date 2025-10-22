<?php

namespace App\Models\Inventario;

use App\Models\User;
use App\Models\Sucursal;


use Illuminate\Database\Eloquent\Model;

class MovimientoInventario extends Model
{
    protected $table = 'movimientos_inventario';
    
    protected $fillable = [
        'tipo',
        'medicamento_id',
        'sucursal_id',
        'lote_id',
        'cantidad',
        'motivo',
        'referencia',
        'user_id',
        'stock_final'
    ];

    public function medicamento()
    {
        return $this->belongsTo(Medicamento::class);
    }

    public function lote()
    {
        return $this->belongsTo(Lote::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
