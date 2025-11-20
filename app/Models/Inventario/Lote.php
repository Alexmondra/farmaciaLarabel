<?php

namespace App\Models\Inventario;

use App\Models\Sucursal;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;
use App\Models\Inventario\MedicamentoSucursal;

class Lote extends Model
{

    protected $fillable = [
        'medicamento_id',
        'sucursal_id',
        'codigo_lote',
        'stock_actual',
        'fecha_vencimiento',
        'ubicacion',
        'precio_compra',
        'precio_oferta',
        'observaciones',
    ];

    protected $casts = [
        'fecha_vencimiento' => 'date',
        'stock_actual' => 'integer',
        'precio_compra' => 'decimal:4',
        'precio_oferta' => 'decimal:2',
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

    // 🔹 RELACIÓN HACIA medicamento_sucursal (para obtener precio_venta)
    public function medicamentoSucursal()
    {
        return $this->hasOne(MedicamentoSucursal::class, 'medicamento_id', 'medicamento_id')
            ->where('sucursal_id', $this->sucursal_id);
    }






    /* ===================== ATRIBUTOS PERSONALIZADOS ===================== */


    public function estaVencido()
    {
        return $this->fecha_vencimiento && $this->fecha_vencimiento->isPast();
    }

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
        return $query->where('stock_actual', '>', 0);
    }
}
