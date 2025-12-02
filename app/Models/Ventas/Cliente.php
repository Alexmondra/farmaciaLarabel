<?php

namespace App\Models\Ventas;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cliente extends Model
{
    use HasFactory;

    protected $table = 'clientes';

    protected $fillable = [
        'tipo_documento',
        'documento',
        'nombre',
        'apellidos',
        'razon_social',
        'sexo',
        'fecha_nacimiento',
        'puntos',
        'telefono',
        'email',
        'direccion',
        'activo',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'activo' => 'boolean',
        'puntos' => 'integer',
    ];

    public function getNombreCompletoAttribute()
    {
        if (!empty($this->razon_social)) {
            return $this->razon_social;
        }
        return trim("{$this->nombre} {$this->apellidos}");
    }


    public function ventas()
    {
        return $this->hasMany(Venta::class);
    }
}
