<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

// AGREGA ESTAS DOS LÍNEAS
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class SucursalActivaScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        // Si hay un usuario logueado
        if (Auth::check()) {
            /** @var \App\Models\User $user */ // <--- AÑADE ESTA LÍNEA
            $user = Auth::user();

            if ($user->hasRole('Administrador')) {
                // Si el admin filtró por una sucursal, la usamos
                if (Session::has('sucursal_id')) { // <--- CORREGIDO
                    $builder->where($model->getTable() . '.sucursal_id', Session::get('sucursal_id')); // <--- CORREGIDO
                }
                return;
            }

            if (session()->has('sucursal_id')) {
                $builder->where($model->getTable() . '.sucursal_id', session('sucursal_id'));
            } else {
                $builder->where($model->getTable() . '.sucursal_id', -1);
            }
        }
    }
}
