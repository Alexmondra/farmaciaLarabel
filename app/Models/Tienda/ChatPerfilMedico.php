<?php

namespace App\Models\Tienda;

use App\Models\Ventas\Cliente;
use Illuminate\Database\Eloquent\Model;

class ChatPerfilMedico extends Model
{
    protected $table = 'chat_perfiles_medicos';

    protected $fillable = [
        'cliente_id',
        'device_fingerprint',
        'antecedentes',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
}
