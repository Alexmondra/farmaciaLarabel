<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sucursal extends Model
{
    protected $table = 'sucursales';

    protected $fillable = ['codigo', 'nombre', 'direccion', 'telefono', 'activo'];
    /*
    public function stocks()
    {
        return $this->hasMany(MedicamentoSucursal::class);
    }

    public function medicamentos()
    {
        return $this->belongsToMany(Medicamento::class, 'medicamento_sucursal')
            ->withPivot('stock', 'stock_min', 'precio_venta', 'ubicacion')
            ->withTimestamps();
    }

    */

    // Usuarios asignados a esta sucursal
    public function usuarios()
    {
        return $this->hasMany(User::class);
    }
}
