<?php

namespace App\Models\Guias;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Inventario\Medicamento;
use App\Models\Inventario\Lote;

class DetalleGuiaRemision extends Model
{
    use HasFactory;

    protected $table = 'detalle_guias_remision';

    protected $fillable = [
        'guia_remision_id',
        'medicamento_id',
        'lote_id',
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

    public function lote()
    {
        return $this->belongsTo(Lote::class, 'lote_id');
    }
}
