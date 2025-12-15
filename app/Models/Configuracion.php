<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    protected $table = 'configuraciones';

    protected $fillable = [
        'empresa_ruc',
        'empresa_razon_social',
        'empresa_direccion',

        'sunat_produccion',
        'sunat_sol_user',
        'sunat_sol_pass',

        'sunat_certificado_path',
        'sunat_certificado_pass',

        // 🔥 GRE (API REST)
        'sunat_client_id',
        'sunat_client_secret',

        'puntos_por_moneda',
        'valor_punto_canje',
        'ruta_logo',
        'mensaje_ticket',
    ];

    protected $casts = [
        'sunat_produccion'   => 'boolean',
        'puntos_por_moneda'  => 'integer',
        'valor_punto_canje'  => 'decimal:4',
    ];
}
