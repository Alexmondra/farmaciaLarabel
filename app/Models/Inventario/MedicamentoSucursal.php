<?php

namespace App\Models\Inventario;

use App\Models\Sucursal;
use App\Models\User; // Agregamos User para la relación updated_by
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Tu migración tiene deleted_at, así que esto es útil

class MedicamentoSucursal extends Model
{
    use HasFactory, SoftDeletes; // Agregamos SoftDeletes para manejar el deleted_at

    protected $table = 'medicamento_sucursal';

    // 1. FILLABLE: Ajustado EXACTAMENTE a tu migración
    protected $fillable = [
        'medicamento_id',
        'sucursal_id',
        'stock_minimo',
        'precio_venta',
        'precio_blister',
        'precio_caja',
        'activo',
        'updated_by',
    ];

    protected $casts = [
        'stock_minimo'   => 'integer',
        'precio_venta'   => 'decimal:2',
        'precio_blister' => 'decimal:2',
        'precio_caja'    => 'decimal:2',
        'activo'         => 'boolean',
        'deleted_at'     => 'datetime',
    ];

    /* ===================== RELACIONES ===================== */

    public function medicamento()
    {
        return $this->belongsTo(Medicamento::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function usuarioActualizacion()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Relación con los Lotes (para calcular el stock real)
    public function lotes()
    {
        return $this->hasMany(Lote::class, 'medicamento_id', 'medicamento_id')
            ->where('sucursal_id', $this->sucursal_id);
    }


    /* ===================== LÓGICA DE NEGOCIO EXTRA ===================== */
    public function getPrecioReporteDigemidAttribute()
    {
        return $this->precio_venta;
    }

    /* ===================== SCOPES (Lógica de Negocio) ===================== */
    /* ===================== SCOPES RECUPERADOS (ESENCIALES PARA VENTAS) ===================== */

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    // 2. scopeBuscar: Usado para filtrar por nombre/código en el buscador de Ventas
    public function scopeBuscar($query, $term)
    {
        return $query->whereHas('medicamento', function ($sub) use ($term) {
            $sub->where('nombre', 'LIKE', "%{$term}%")
                ->orWhere('codigo', 'LIKE', "%{$term}%")
                ->orWhere('laboratorio', 'LIKE', "%{$term}%");
        });
    }

    /* ===================== SCOPE DE ALERTAS (EL NUEVO) ===================== */
    public function scopeConStockBajo($query, $sucursalId = null)
    {
        $sqlStockReal = '(SELECT COALESCE(SUM(stock_actual), 0) 
                          FROM lotes 
                          WHERE lotes.medicamento_id = medicamento_sucursal.medicamento_id 
                          AND lotes.sucursal_id = medicamento_sucursal.sucursal_id)';

        $query->select('medicamento_sucursal.*');
        $query->selectRaw("{$sqlStockReal} as stock_computado");
        $query->when($sucursalId, function ($q) use ($sucursalId) {
            $q->where('sucursal_id', $sucursalId);
        });
        $query->where('activo', true);

        return $query->whereRaw("{$sqlStockReal} <= medicamento_sucursal.stock_minimo");
    }
}
