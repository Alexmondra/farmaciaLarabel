<?php

namespace App\Models\Seguridad;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class PersonalChatLimite extends Model
{
    protected $table = 'personal_chat_limites';

    protected $fillable = [
        'user_id',
        'limite_diario',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
