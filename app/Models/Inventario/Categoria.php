<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $fillable = ['nombre', 'descripcion', 'activo'];

    public function medicamentos()
    {
        return $this->hasMany(Medicamento::class);
    }
}
