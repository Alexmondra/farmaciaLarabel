<?php

namespace App\Models\Tienda;

use Illuminate\Database\Eloquent\Model;

class MensajeConversacion extends Model
{
    protected $table = 'chat_mensajes';

    protected $fillable = [
        'conversacion_id',
        'role',
        'content',
    ];

    public function conversacion()
    {
        return $this->belongsTo(Conversacion::class, 'conversacion_id');
    }
}
