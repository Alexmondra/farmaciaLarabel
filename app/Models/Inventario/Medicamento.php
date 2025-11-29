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
        'activo'
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
            ->withPivot('precio_venta', 'deleted_at')
            ->wherePivot('deleted_at', null) // y otros campos si tienes
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
}
