<?php

namespace App\Models\Tienda;

use App\Models\Ventas\Cliente;
use Illuminate\Database\Eloquent\Model;

class Conversacion extends Model
{
    protected $table = 'chat_conversaciones';

    protected $fillable = [
        'cliente_id',
        'device_fingerprint',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function mensajes()
    {
        return $this->hasMany(MensajeConversacion::class, 'conversacion_id');
    }
}
