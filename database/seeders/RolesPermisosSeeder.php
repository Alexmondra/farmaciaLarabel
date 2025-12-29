<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

class RolesPermisosSeeder extends Seeder
{

    public function run(): void
    {
        // Limpia cache de permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // === LISTA LIMPIA DE PERMISOS PARA FARMACIA ===
        $permisos = [
            // 1. VENTAS
            'ventas.ver',
            'ventas.crear',
            'ventas.anular',

            // 2. CAJAS
            'cajas.ver',
            'cajas.abrir',
            'cajas.cerrar',

            // 3. GUÍAS DE REMISIÓN
            'guias.ver',
            'guias.crear',

            // 4. FACTURACIÓN SUNAT
            'sunat.monitor',
            'sunat.archivos',

            // 5. REPORTES
            'reportes.ver',
            'reportes.ventas',
            'reportes.inventario',
            'reportes.digemid',
            //5.5. Ajustes
            'lotes.ver',
            'stock.ajustar',

            // 6. CLIENTES
            'clientes.ver',
            'clientes.crear',
            'clientes.editar',
            'clientes.eliminar',

            // 7. MEDICAMENTOS (INVENTARIO)
            'medicamentos.ver',
            'medicamentos.crear',
            'medicamentos.editar',
            'medicamentos.eliminar',
            'medicamentos.global',

            // 8. CATEGORÍAS
            'categorias.ver',
            'categorias.crear',
            'categorias.editar',
            'categorias.eliminar',

            // 9. VENCIMIENTOS
            'vencimiento.ver',

            // 10. COMPRAS
            'compras.ver',
            'compras.crear',
            'compras.editar',
            'compras.anular',

            // 11. PROVEEDORES
            'proveedores.ver',
            'proveedores.crear',
            'proveedores.editar',
            'proveedores.eliminar',

            // 12. USUARIOS
            'usuarios.ver',
            'usuarios.crear',
            'usuarios.editar',
            'usuarios.eliminar',

            // 13. ROLES
            'roles.ver',
            'roles.crear',
            'roles.editar',
            'roles.eliminar',

            // 14. PERMISOS
            'permisos.ver',
            'permisos.asignar',
            'permisos.revocar',

            // 15. SUCURSALES
            'sucursales.ver',
            'sucursales.crear',
            'sucursales.editar',
            'sucursales.eliminar',

            // 16. CONFIGURACIÓN GENERAL
            'config.ver',
            'config.editar',
        ];

        foreach ($permisos as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        // === ROLES POR DEFECTO ===

        // 1. Administrador (Tiene TODO)
        $admin = Role::firstOrCreate(['name' => 'Administrador']);
        $admin->syncPermissions(Permission::all());

        // 2. Vendedor (Solo vende y ve clientes)
        $vendedor = Role::firstOrCreate(['name' => 'Vendedor']);
        $vendedor->syncPermissions([
            'ventas.ver',
            'ventas.crear',
            'cajas.abrir',
            'clientes.ver',
            'clientes.crear',
            'medicamentos.ver', // Necesita ver para buscar precios
        ]);

        // 3. Farmacéutico/Almacenero (Controla Stock y Compras)
        $farmaceutico = Role::firstOrCreate(['name' => 'Farmacéutico']);
        $farmaceutico->syncPermissions([
            'medicamentos.ver',
            'medicamentos.crear',
            'medicamentos.editar',
            'categorias.ver',
            'lotes.ver',
            'compras.ver',
            'compras.crear',
            'proveedores.ver',
            'proveedores.crear',
            'stock.ajustar'
        ]);

        // Asignar Admin al primer usuario (TÚ)
        $user = User::first();
        if ($user) {
            $user->assignRole($admin);
        }
    }
}
