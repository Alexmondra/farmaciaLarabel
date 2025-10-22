<?php

namespace App\Models\Inventario;

use App\Models\Sucursal;


use Illuminate\Database\Eloquent\Model;

class Lote extends Model
{
    protected $fillable = [
        'medicamento_id',
        'sucursal_id',
        'codigo_lote',
        'fecha_vencimiento',
        'cantidad_inicial',
        'cantidad_actual',
        'estado'
    ];

    public function medicamento()
    {
        return $this->belongsTo(Medicamento::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoInventario::class);
    }
}
