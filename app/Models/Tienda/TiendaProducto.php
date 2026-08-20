<?php

namespace App\Models\Tienda;

use App\Models\Inventario\Medicamento;
use App\Models\Sucursal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TiendaProducto extends Model
{
    protected $table = 'tienda_productos';

    protected $fillable = [
        'medicamento_id',
        'sucursal_id',
        'slug',
        'nombre_web',
        'descripcion_web',
        'precio_web',
        'stock_modo',
        'stock_web',
        'visible',
        'destacado',
    ];

    protected $casts = [
        'precio_web' => 'decimal:2',
        'stock_web' => 'integer',
        'visible' => 'boolean',
        'destacado' => 'boolean',
    ];

    public const STOCK_SIN_CONTROL = 'sin_control';
    public const STOCK_MANUAL = 'stock_manual';
    public const STOCK_SUCURSAL = 'stock_sucursal';

    public function medicamento()
    {
        return $this->belongsTo(Medicamento::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function imagenes()
    {
        return $this->hasMany(TiendaProductoImagen::class, 'tienda_producto_id')->orderBy('orden')->orderBy('id');
    }

    public function imagenesVisibles()
    {
        return $this->imagenes()->where('visible', true);
    }

    public function getNombreAttribute(): string
    {
        return $this->nombre_web ?: ($this->medicamento->nombre ?? 'Producto');
    }

    public function getImagenUrlAttribute(): ?string
    {
        $path = $this->medicamento->imagen_path ?? null;

        if (!$path) {
            $path = $this->imagenesVisibles->first()?->imagen_path;
        }

        if (!$path) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://', '/'])) {
            return $path;
        }

        return asset('storage/' . ltrim($path, '/'));
    }

    public function getGaleriaImagenesAttribute(): array
    {
        $imagenes = [];

        if ($this->medicamento?->imagen_path) {
            $path = $this->medicamento->imagen_path;
            $imagenes[] = [
                'url' => Str::startsWith($path, ['http://', 'https://', '/']) ? $path : asset('storage/' . ltrim($path, '/')),
                'alt' => $this->nombre,
                'principal' => true,
            ];
        }

        foreach ($this->imagenesVisibles as $imagen) {
            $imagenes[] = [
                'url' => $imagen->url,
                'alt' => $imagen->alt ?: $this->nombre,
                'principal' => false,
            ];
        }

        return $imagenes;
    }

    public function precioVenta(): float
    {
        if (isset($this->precio_sucursal)) {
            $precio = $this->precio_web ?: $this->precio_sucursal;
            return (float) ($precio ?: 0);
        }

        $precio = $this->precio_web ?: DB::table('medicamento_sucursal')
            ->where('medicamento_id', $this->medicamento_id)
            ->where('sucursal_id', $this->sucursal_id)
            ->value('precio_venta');

        return (float) ($precio ?: 0);
    }

    public function stockDisponible(): ?int
    {
        return match ($this->stock_modo) {
            self::STOCK_SIN_CONTROL => null,
            self::STOCK_MANUAL => (int) ($this->stock_web ?? 0),
            default => (int) DB::table('lotes')
                ->where('medicamento_id', $this->medicamento_id)
                ->where('sucursal_id', $this->sucursal_id)
                ->where('stock_actual', '>', 0)
                ->sum('stock_actual'),
        };
    }

    public function permiteCantidad(int $cantidad): bool
    {
        $stock = $this->stockDisponible();

        return $stock === null || $cantidad <= $stock;
    }
}
