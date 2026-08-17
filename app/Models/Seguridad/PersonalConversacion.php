<?php

namespace App\Models\Seguridad;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PersonalConversacion extends Model
{
    protected $table = 'personal_conversaciones';

    protected $fillable = [
        'user_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function mensajes()
    {
        return $this->hasMany(PersonalMensaje::class, 'conversacion_id');
    }
}
