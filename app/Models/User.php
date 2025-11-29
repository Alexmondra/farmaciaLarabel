<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles;
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        // --- NUEVOS CAMPOS ---
        'documento',
        'telefono',
        'direccion',
        'imagen_perfil',
        'activo',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean', // Para manejarlo como true/false directo
        ];
    }

    // --- RELACIONES CON SUCURSALES (Tus funciones originales) ---

    public function sucursales()
    {
        return $this->belongsToMany(Sucursal::class, 'sucursal_user') // Asegúrate que tu tabla pivote se llame así
            ->withTimestamps();
    }

    public function tieneSucursal(int $sucursalId): bool
    {
        return $this->sucursales->contains('id', $sucursalId); // Optimizado un poco
    }

    public function sucursalActivaId(): ?int
    {
        return session('sucursal_id');
    }

    // Helper para obtener la URL de la imagen o una por defecto
    public function getImagenUrlAttribute()
    {
        if ($this->imagen_perfil && file_exists(public_path('storage/' . $this->imagen_perfil))) {
            return asset('storage/' . $this->imagen_perfil);
        }
        // Avatar por defecto con las iniciales (UI Avatars)
        return 'https://robohash.org/' . urlencode($this->id);
    }
}
