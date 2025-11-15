<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Inventario\Medicamento;
use App\Policies\MedicamentoPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Medicamento::class => MedicamentoPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
