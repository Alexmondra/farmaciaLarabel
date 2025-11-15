<?php

namespace App\Models\Inventario;

use App\Models\Sucursal;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class MedicamentoSucursal extends Model
{
    use HasFactory;

    protected $table = 'medicamento_sucursal';



    protected $fillable = [
        'medicamento_id',
        'sucursal_id',
        'stock_total',
        'precio_venta',
        'deleted_at',
    ];


    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    public function isActive()
    {
        return is_null($this->deleted_at);
    }
    /* ===================== RELACIONES ===================== */

    // Un registro pertenece a una sucursal
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    // Un medicamento en una sucursal tiene muchos lotes
    public function lotes()
    {
        return $this->hasMany(Inventario\Lote::class, 'medicamento_id', 'medicamento_id')
            ->where('sucursal_id', $this->sucursal_id);
    }

    /* ===================== SCOPES ===================== */

    // Solo activos
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    // Buscar por nombre del medicamento o código
    public function scopeBuscar($query, $texto)
    {
        return $query->whereHas('medicamento', function ($q) use ($texto) {
            $q->where('nombre', 'like', "%$texto%")
                ->orWhere('codigo', 'like', "%$texto%");
        });
    }
}
