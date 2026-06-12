# Farmacia Laravel

Sistema web para la gestión operativa de una farmacia con inventario por sucursales, control de lotes, ventas, compras, guías y reportes.

El proyecto está construido con Laravel y está orientado a mantener trazabilidad del stock mediante lotes, vencimientos y movimientos de inventario.

## Módulos Principales

- Inventario de medicamentos.
- Stock por sucursal y por lote.
- Ajustes de entrada y salida.
- Distribución de lotes entre sucursales.
- Precios por sucursal.
- Categorías de medicamentos.
- Compras y proveedores.
- Ventas y caja.
- Guías de remisión.
- Reportes de stock, vencimientos y ventas.
- Gestión de usuarios, roles y permisos.

## Modelo De Inventario

El stock real se maneja en la tabla de lotes. Cada lote pertenece a un medicamento y a una sucursal:

```text
lotes
- medicamento_id
- sucursal_id
- codigo_lote
- fecha_vencimiento
- stock_actual
```

La tabla `medicamento_sucursal` guarda la configuración del medicamento en cada sucursal:

```text
medicamento_sucursal
- precio_venta
- precio_blister
- precio_caja
- stock_minimo
- activo
```

Esto permite separar correctamente el stock físico de la configuración comercial por tienda.

## Reglas Importantes

- Los lotes vencidos se conservan para historial, pero no se consideran stock vendible.
- Los lotes con `stock_actual = 0` se mantienen para trazabilidad.
- El stock disponible se calcula sumando lotes vigentes con stock mayor a cero.
- Los precios pueden variar por sucursal.
- La categoría pertenece al medicamento general, no a cada sucursal.

## Instalación Básica

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
```

Para desarrollo local:

```bash
php artisan serve
npm run dev
```

## Comandos Útiles

```bash
php artisan migrate
php artisan db:seed
php artisan view:clear
php artisan cache:clear
php artisan route:list
```

## Notas De Mantenimiento

- No eliminar medicamentos físicamente si ya tienen ventas, lotes o movimientos asociados.
- Para duplicados, se recomienda unificar datos y desactivar el registro duplicado.
- Antes de operaciones masivas sobre inventario, realizar backup de base de datos.
- Cualquier ajuste directo en SQL debe ejecutarse dentro de una transacción.

## Tecnologías

- Laravel
- MySQL/MariaDB
- Blade
- jQuery
- Bootstrap/AdminLTE
- Vite
