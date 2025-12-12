<?php

namespace App\Models\Ventas;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Sucursal; // Ajusta el namespace según donde tengas tu modelo Sucursal

class NotaCredito extends Model
{
    use HasFactory;

    protected $table = 'notas_credito';

    protected $fillable = [
        'venta_id',
        'sucursal_id',
        'serie',
        'numero',
        'fecha_emision',
        'tipo_nota',
        'tipo_moneda',
        'cod_motivo',
        'descripcion_motivo',
        'ruta_xml',
        'ruta_cdr',
        'ruta_pdf',
        'hash',
        'sunat_exito',
        'mensaje_sunat',
        'codigo_error_sunat',
    ];

    protected $casts = [
        'fecha_emision' => 'datetime',
        'sunat_exito'   => 'boolean',
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }
}
