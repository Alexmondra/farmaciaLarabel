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
    
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function sucursales()
    {
        return $this->belongsToMany(Sucursal::class)->withTimestamps();
    }

    // ¿tiene acceso a esta sucursal?
    public function tieneSucursal(int $sucursalId): bool
    {
        return $this->sucursales->pluck('id')->contains($sucursalId);
    }

    // sucursal activa desde sesión (puede ser null)
    public function sucursalActivaId(): ?int
    {
        return session('sucursal_id');
    }




    

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
