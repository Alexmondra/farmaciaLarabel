<?php

namespace App\Models\Tienda;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TiendaProductoImagen extends Model
{
    protected $table = 'tienda_producto_imagenes';

    protected $fillable = [
        'tienda_producto_id',
        'imagen_path',
        'alt',
        'orden',
        'visible',
    ];

    protected $casts = [
        'orden' => 'integer',
        'visible' => 'boolean',
    ];

    public function producto()
    {
        return $this->belongsTo(TiendaProducto::class, 'tienda_producto_id');
    }

    public function getUrlAttribute(): string
    {
        if (Str::startsWith($this->imagen_path, ['http://', 'https://', '/'])) {
            return $this->imagen_path;
        }

        return asset('storage/' . ltrim($this->imagen_path, '/'));
    }
}
