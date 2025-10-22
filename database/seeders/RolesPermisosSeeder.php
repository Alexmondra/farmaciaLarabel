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

        // === Permisos (alineados a tu menú) ===
        $permisos = [
            // POS / Ventas
            'pos.ver',
            'ventas.ver',
            'ventas.crear',
            'ventas.editar',
            'ventas.anular',
            'devoluciones.ver',
            'devoluciones.crear',
            'cajas.ver',
            'cajas.cerrar',

            // Facturación SUNAT
            'comprobantes.ver',
            'facturas.crear',
            'boletas.crear',
            'notas.ver',
            'notas.crear',
            'sunat.envios',
            'sunat.estado',
            'sunat.resumenes',

            // Guías
            'guias.ver',
            'guias.crear',

            // Inventario
            'medicamentos.ver',
            'medicamentos.crear',
            'medicamentos.editar',
            'medicamentos.borrar',
            'categorias.ver',
            'unidades.ver',
            'lotes.ver',
            'kardex.ver',
            'inventarios.ver',

            // Compras / Proveedores
            'proveedores.ver',
            'proveedores.crear',
            'proveedores.editar',
            'compras.ver',
            'ordenes.ver',
            'ingresos.ver',

            // Clientes / Recetas
            'clientes.ver',
            'clientes.crear',
            'recetas.ver',
            'recetas.crear',

            // Reportes
            'reportes.ver',
            'reportes.ventas',
            'reportes.medicamentos',
            'reportes.impuestos',
            'reportes.stock',
            'reportes.venc',
            'reportes.compras',

            // Seguridad / Config
            'usuarios.ver',
            'usuarios.crear',
            'usuarios.editar',
            'usuarios.borrar',
            'roles.ver',
            'roles.crear',
            'roles.editar',
            'roles.borrar',
            'permisos.ver',
            //'auditoria.ver',
            'config.ver',
            'config.empresa',
            'config.series',
            'config.impuestos',
            'config.sunat',
            'config.infra',

            // parametros de sucursales

            'sucursales.ver',
            'sucursales.crear',
            'sucursales.editar',
            'sucursales.borrar',

        ];

        foreach ($permisos as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        // === Roles (ejemplo) ===
        $admin       = Role::firstOrCreate(['name' => 'Administrador']);
        $vendedor    = Role::firstOrCreate(['name' => 'Vendedor']);
        $almacenero  = Role::firstOrCreate(['name' => 'Almacenero']);
        $contador    = Role::firstOrCreate(['name' => 'Contador']);
        $supervisor  = Role::firstOrCreate(['name' => 'Supervisor']);

        // Admin: todos los permisos
        $admin->syncPermissions(Permission::all());

        // Vendedor: POS y ventas
        $vendedor->syncPermissions([
            'pos.ver',
            'ventas.ver',
            'ventas.crear',
            'ventas.editar',
            'ventas.anular',
            'devoluciones.ver',
            'devoluciones.crear',
            'cajas.ver',
            'cajas.cerrar',
            'comprobantes.ver',
            'facturas.crear',
            'boletas.crear',
            'clientes.ver',
            'clientes.crear',
            'reportes.ver',
            'reportes.ventas',
            'reportes.medicamentos',
        ]);

        // Almacenero: inventario + compras
        $almacenero->syncPermissions([
            'medicamentos.ver',
            'medicamentos.crear',
            'medicamentos.editar',
            'medicamentos.borrar',
            'categorias.ver',
            'unidades.ver',
            'lotes.ver',
            'kardex.ver',
            'inventarios.ver',
            'proveedores.ver',
            'proveedores.crear',
            'proveedores.editar',
            'compras.ver',
            'ordenes.ver',
            'ingresos.ver',
            'guias.ver',
            'guias.crear',
            'reportes.ver',
            'reportes.stock',
            'reportes.venc',
            'reportes.compras',
        ]);

        // Contador: facturación y reportes impuestos
        $contador->syncPermissions([
            'comprobantes.ver',
            'sunat.envios',
            'sunat.estado',
            'sunat.resumenes',
            'reportes.ver',
            'reportes.impuestos',
        ]);

        // Supervisor: lectura general y reportes
        $supervisor->syncPermissions([
            'ventas.ver',
            'comprobantes.ver',
            'medicamentos.ver',
            'proveedores.ver',
            'clientes.ver',
            'reportes.ver',
            'reportes.ventas',
            'reportes.medicamentos',
            'reportes.stock',
        ]);

        // Usuario admin inicial (ajusta email)
        $user = User::first();
        if ($user) {
            $user->syncRoles([$admin]); // lo haces admin para empezar
        }
    }
}
