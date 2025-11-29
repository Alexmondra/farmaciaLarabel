<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sucursal extends Model
{
    protected $table = 'sucursales';

    protected $fillable = [
        'codigo',
        'nombre',
        'direccion',
        'telefono',
        'imagen_sucursal',
        'impuesto_porcentaje',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean',
        'impuesto_porcentaje' => 'decimal:2', // Asegura que siempre devuelva 18.00 y no "18.00" (string)
    ];

    public function usuarios()
    {
        return $this->hasMany(User::class);
    }
}
