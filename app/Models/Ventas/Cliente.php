<?php

namespace App\Models\Ventas;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cliente extends Authenticatable
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
        'tienda_password',
        'tienda_email_verified_at',
        'tienda_last_login_at',
        'direccion',
        'activo',
    ];

    protected $hidden = [
        'tienda_password',
        'remember_token',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'tienda_email_verified_at' => 'datetime',
        'tienda_last_login_at' => 'datetime',
        'activo' => 'boolean',
        'puntos' => 'integer',
    ];

    public function getAuthPassword()
    {
        return $this->tienda_password;
    }

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

    public function pedidosOnline()
    {
        return $this->hasMany(\App\Models\Tienda\PedidoOnline::class);
    }

    public function getPuntosAttribute($value)
    {
        if ($this->id === 1) {
            return 0;
        }

        return $value;
    }
}
