<?php

namespace App\Models\Inventario;

use App\Models\User;
use App\Models\Sucursal;

use Illuminate\Database\Eloquent\Model;

class Medicamento extends Model
{
    protected $fillable = [
        'codigo',
        'nombre',
        'forma_farmaceutica',
        'concentracion',
        'presentacion',
        'laboratorio',
        'registro_sanitario',
        'codigo_barra',
        'descripcion',
        'unidades_por_envase',
        'imagen_path',
        'categoria_id',
        'user_id',
        'activo',
        // --- NUEVO CAMPO IMPORTANTE ---
        'afecto_igv', // true = Paga IGV (Normal), false = Exonerado (Cáncer, etc.)
    ];

    protected $casts = [
        'activo' => 'boolean',
        'afecto_igv' => 'boolean', // <--- ¡Importante para que funcione tu if()!
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sucursales()
    {
        return $this->belongsToMany(\App\Models\Sucursal::class, 'medicamento_sucursal')
            ->withPivot('precio_venta', 'stock_minimo', 'deleted_at')
            ->wherePivot('deleted_at', null)
            ->withTimestamps();
    }

    public function lotes()
    {
        return $this->hasMany(Lote::class);
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoInventario::class);
    }

    public function scopeEnSucursal($query, int $sucursalId)
    {
        return $query->whereHas('sucursales', fn($q) => $q->where('sucursal_id', $sucursalId));
    }

    public function medicamentos()
    {
        return $this->belongsToMany(
            \App\Models\Inventario\Medicamento::class,
            'medicamento_sucursal'
        )->withPivot(['precio_venta', 'deleted_at'])
            ->withTimestamps();
    }


    public function scopeConStockBajo($query, $sucursalId = null)
    {
        $query->when($sucursalId, function ($q) use ($sucursalId) {
            $q->where('sucursal_id', $sucursalId);
        });

        $query->where('activo', true);
        return $query->whereRaw(
            '(SELECT COALESCE(SUM(stock_actual), 0) 
              FROM lotes 
              WHERE lotes.medicamento_id = medicamento_sucursal.medicamento_id 
              AND lotes.sucursal_id = medicamento_sucursal.sucursal_id) <= medicamento_sucursal.stock_minimo'
        )->whereRaw(
            '(SELECT COALESCE(SUM(stock_actual), 0) 
              FROM lotes 
              WHERE lotes.medicamento_id = medicamento_sucursal.medicamento_id 
              AND lotes.sucursal_id = medicamento_sucursal.sucursal_id) > 0'
        );
    }
}
