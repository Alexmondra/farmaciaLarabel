<?php

namespace App\Models\Seguridad;

use Illuminate\Database\Eloquent\Model;

class PersonalMensaje extends Model
{
    protected $table = 'personal_mensajes';

    protected $fillable = [
        'conversacion_id',
        'role',
        'content',
    ];

    public function conversacion()
    {
        return $this->belongsTo(PersonalConversacion::class, 'conversacion_id');
    }
}
