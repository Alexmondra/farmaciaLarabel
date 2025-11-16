<?php

namespace App\Models\Compras; // O App\Models, según tu estructura

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proveedor extends Model
{
    use HasFactory;

    /**
     * El nombre de la tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'proveedores';

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'razon_social', //
        'ruc',          //
        'direccion',    //
        'telefono',     //
        'email',        //
        'activo',       //
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'activo' => 'boolean', //
    ];


    // Obtiene las compras asociadas a este proveedor.



    public function compras()
    {
        return $this->hasMany(Compra::class);
    }
}
