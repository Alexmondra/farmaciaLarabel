<?php

namespace App\Models\Inventario;

use App\Models\Sucursal;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class Lote extends Model
{
    protected $fillable = [
        'medicamento_id',
        'sucursal_id',
        'codigo_lote',
        'cantidad',
        'fecha_vencimiento',
        'ubicacion',
        'precio_compra',
        'precio_oferta',
        'observaciones',
    ];

    protected $casts = [
        'fecha_vencimiento' => 'date',
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






    /* ===================== ATRIBUTOS PERSONALIZADOS ===================== */

    // Precio efectivo: usa la oferta si existe, de lo contrario el precio base
    public function getPrecioVentaEfectivoAttribute()
    {
        return $this->precio_oferta
            ?? optional($this->medicamentoSucursal)->precio_venta;
    }

    // Estado según vencimiento
    public function getEstadoVencimientoAttribute()
    {
        if (!$this->fecha_vencimiento) return 'Sin fecha';
        $hoy = Carbon::today();
        if ($this->fecha_vencimiento->isPast()) return 'Vencido';
        if ($this->fecha_vencimiento->diffInDays($hoy) <= 30) return 'Por vencer';
        return 'Vigente';
    }

    /* ===================== SCOPES ===================== */

    // Ordenar por vencimiento (FEFO)
    public function scopeFefo($query)
    {
        return $query->orderByRaw('fecha_vencimiento IS NULL, fecha_vencimiento ASC');
    }

    // Solo lotes con stock disponible
    public function scopeConStock($query)
    {
        return $query->where('cantidad', '>', 0);
    }
}
