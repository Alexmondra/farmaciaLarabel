<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sucursal extends Model
{
    protected $table = 'sucursales';

    protected $fillable = [
        'codigo',
        'nombre',
        'ubigeo',
        'departamento',
        'provincia',
        'distrito',
        'direccion',
        'telefono',
        'email',
        'imagen_sucursal',
        'impuesto_porcentaje',
        'serie_boleta',
        'serie_factura',
        'serie_ticket',
        'serie_nc_boleta',
        'serie_nc_factura',
        'serie_guia',
        'cod_establecimiento_digemid',

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
