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
            // 1. VENTAS Y POS
            'ventas.ver',       // Ver historial y dashboard de ventas
            'ventas.crear',     // Acceso al POS / Registrar venta
            'ventas.anular',    // Permitir anular una venta
            'cajas.ver',        // Ver estado de cajas
            'cajas.abrir',      // Abrir/Cerrar caja
            'clientes.ver',     // Ver lista de clientes
            'clientes.crear',   // Registrar clientes
            'clientes.editar',   // Registrar clientes
            'clientes.eliminar',   // Registrar clientes


            // 2. INVENTARIO (Medicamentos)
            'medicamentos.ver',     // Ver lista y stock
            'medicamentos.crear',   // Agregar nuevos productos
            'medicamentos.editar',  // Editar precios/datos
            'medicamentos.eliminar', // Borrar productos
            'categorias.ver',       // Gestionar categorías
            'lotes.ver',            // Ver vencimientos y lotes
            'stock.ajustar',        // Hacer ajustes manuales de stock (pérdidas, etc)

            // 3. COMPRAS (Proveedores)
            'compras.ver',          // Ver historial de compras
            'compras.crear',        // Registrar ingreso de mercadería
            'proveedores.ver',      // Ver proveedores
            'proveedores.crear',    // Gestionar proveedores
            'proveedores.editar',
            'proveedores.eliminar',
            // 4. SEGURIDAD (Usuarios y Roles)
            'usuarios.ver',
            'usuarios.crear',
            'usuarios.editar',
            'usuarios.eliminar',
            'roles.ver',            // Ver panel de roles
            'roles.crear',          // Crear nuevos roles
            'roles.editar',         // Asignar permisos a roles
            'roles.eliminar',

            // 5. REPORTES Y CONFIGURACIÓN
            'reportes.ver',         // Acceso general a reportes
            'config.ver',           // Ver configuración del sistema
            'config.editar',        // Editar datos de la empresa/impresora

            // 3. COMPRAS (Proveedores)
            'sucursales.ver',
            'sucursales.crear',
            'sucursales.editar',
            'sucursales.eliminar',
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
