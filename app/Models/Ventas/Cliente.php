<?php

namespace App\Models\Ventas;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Ventas\Venta;


class Cliente extends Model
{
    use HasFactory;

    protected $table = 'clientes';

    protected $fillable = [
        'tipo_documento',
        'documento',
        'nombre',
        'apellidos',
        'sexo',
        'fecha_nacimiento',
        'telefono',
        'email',
        'direccion',
        'activo',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'activo' => 'boolean',
    ];

    /** Relación con ventas (cuando ya tengas la tabla ventas) */
    public function ventas()
    {
        return $this->hasMany(Venta::class, 'cliente_id');
    }
}
