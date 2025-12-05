<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sucursal extends Model
{
    protected $table = 'sucursales';

    protected $fillable = [
        'codigo',   // Ahora es el Código Anexo SUNAT (0000, 0001...)
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

        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean',
        'impuesto_porcentaje' => 'decimal:2',
    ];

    // Relaciones
    public function usuarios()
    {
        return $this->hasMany(User::class);
    }
}
