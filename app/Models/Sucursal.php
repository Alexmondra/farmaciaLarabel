<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sucursal extends Model
{
    protected $table = 'sucursales';

    protected $fillable = [
        'codigo',
        'nombre',
        'serie_boleta',
        'serie_factura',
        'serie_ticket',
        'direccion',
        'telefono',
        'imagen_sucursal',
        'impuesto_porcentaje',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean',
        'impuesto_porcentaje' => 'decimal:2',
    ];

    public function usuarios()
    {
        return $this->hasMany(User::class);
    }
}
