<?php

namespace App\Models\Guias;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Inventario\Medicamento;

class DetalleGuiaRemision extends Model
{
    use HasFactory;

    protected $table = 'detalle_guias_remision';

    protected $fillable = [
        'guia_remision_id',
        'medicamento_id',
        'codigo_producto',
        'descripcion',
        'unidad_medida',
        'cantidad',
    ];

    protected $casts = [
        'cantidad' => 'decimal:4',
    ];

    public function guia()
    {
        return $this->belongsTo(GuiaRemision::class, 'guia_remision_id');
    }

    public function medicamento()
    {
        return $this->belongsTo(Medicamento::class, 'medicamento_id');
    }
}
