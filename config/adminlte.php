<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Title
    |--------------------------------------------------------------------------
    */

    'title' => 'FarmaciaSys', // Nombre más profesional
    'title_prefix' => '',
    'title_postfix' => '',

    /*
    |--------------------------------------------------------------------------
    | Favicon
    |--------------------------------------------------------------------------
    */

    'use_ico_only' => false,
    'use_full_favicon' => false,

    /*
    |--------------------------------------------------------------------------
    | Google Fonts
    |--------------------------------------------------------------------------
    */

    'google_fonts' => [
        'allowed' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Logo
    |--------------------------------------------------------------------------
    */

    'logo' => '<i class="fas fa-clinic-medical mr-1"></i> <b>Farma</b>Sys',
    'logo_img' => 'vendor/adminlte/dist/img/AdminLTELogo.png',
    'logo_img_class' => 'brand-image img-circle elevation-3',
    'logo_img_xl' => null,
    'logo_img_xl_class' => 'brand-image-xs',
    'logo_img_alt' => 'Farmacia Logo',

    /*
    |--------------------------------------------------------------------------
    | Authentication Logo
    |--------------------------------------------------------------------------
    */

    'auth_logo' => [
        'enabled' => false,
        'img' => [
            'path' => 'vendor/adminlte/dist/img/AdminLTELogo.png',
            'alt' => 'Auth Logo',
            'class' => '',
            'width' => 50,
            'height' => 50,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Preloader Animation
    |--------------------------------------------------------------------------
    */

    'preloader' => [
        'enabled' => true,
        'mode' => 'fullscreen',
        'img' => [
            'path' => 'vendor/adminlte/dist/img/AdminLTELogo.png',
            'alt' => 'Cargando...',
            'effect' => 'animation__pulse', // Efecto más sutil
            'width' => 60,
            'height' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Menu
    |--------------------------------------------------------------------------
    */

    'usermenu_enabled' => true,
    'usermenu_header' => true, // Activado para ver perfil
    'usermenu_header_class' => 'bg-teal', // Color farmacia
    'usermenu_image' => false,
    'usermenu_desc' => false,
    'usermenu_profile_url' => false,

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    */

    'layout_topnav' => null,
    'layout_boxed' => null,
    'layout_fixed_sidebar' => true, // Fijo para mejor UX
    'layout_fixed_navbar' => true,
    'layout_fixed_footer' => null,
    'layout_dark_mode' => null,

    /*
    |--------------------------------------------------------------------------
    | Authentication Views Classes
    |--------------------------------------------------------------------------
    */

    'classes_auth_card' => 'card-outline card-teal', // Borde verde azulado
    'classes_auth_header' => '',
    'classes_auth_body' => '',
    'classes_auth_footer' => '',
    'classes_auth_icon' => '',
    'classes_auth_btn' => 'btn-flat btn-teal', // Botones verdes

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Classes
    |--------------------------------------------------------------------------
    */

    'classes_body' => '',
    'classes_brand' => 'bg-teal', // Fondo del logo en verde farmacia
    'classes_brand_text' => '',
    'classes_content_wrapper' => '',
    'classes_content_header' => '',
    'classes_content' => '',
    'classes_sidebar' => 'sidebar-dark-primary elevation-1', // Sidebar oscura con hover teal
    'classes_sidebar_nav' => '',
    'classes_topnav' => 'navbar-white navbar-light',
    'classes_topnav_nav' => 'navbar-expand',
    'classes_topnav_container' => 'container',

    /*
    |--------------------------------------------------------------------------
    | Sidebar
    |--------------------------------------------------------------------------
    */

    'sidebar_mini' => 'lg',
    'sidebar_collapse' => false,
    'sidebar_collapse_auto_size' => false,
    'sidebar_collapse_remember' => false,
    'sidebar_collapse_remember_no_transition' => true,
    'sidebar_scrollbar_theme' => 'os-theme-light',
    'sidebar_scrollbar_auto_hide' => 'l',
    'sidebar_nav_accordion' => true,
    'sidebar_nav_animation_speed' => 300,

    /*
    |--------------------------------------------------------------------------
    | Control Sidebar (Right Sidebar)
    |--------------------------------------------------------------------------
    */

    'right_sidebar' => false,
    'right_sidebar_icon' => 'fas fa-cogs',
    'right_sidebar_theme' => 'dark',
    'right_sidebar_slide' => true,
    'right_sidebar_push' => true,
    'right_sidebar_scrollbar_theme' => 'os-theme-light',
    'right_sidebar_scrollbar_auto_hide' => 'l',

    /*
    |--------------------------------------------------------------------------
    | URLs
    |--------------------------------------------------------------------------
    */

    'use_route_url' => false,
    'dashboard_url' => '/',
    'logout_url' => 'logout',
    'login_url' => 'login',
    'register_url' => 'register',
    'password_reset_url' => 'password/reset',
    'password_email_url' => 'password/email',
    'profile_url' => false,
    'disable_darkmode_routes' => false,

    /*
    |--------------------------------------------------------------------------
    | Laravel Asset Bundling
    |--------------------------------------------------------------------------
    */

    'laravel_asset_bundling' => false,
    'laravel_css_path' => 'css/app.css',
    'laravel_js_path' => 'js/app.js',

    /*
    |--------------------------------------------------------------------------
    | Menu Items (OPTIMIZADO)
    |--------------------------------------------------------------------------
    */

    'menu' => [
        [
            'type' => 'fullscreen-widget',
            'topnav_right' => true,
        ],
        [
            'type' => 'darkmode-widget',
            'topnav_right' => true,
            'icon_enabled' => 'fas fa-moon',
            'icon_disabled' => 'far fa-moon',
        ],

        // --- INICIO DEL MENÚ LATERAL ---

        // 1. DASHBOARD (Limpio, sin etiquetas de colores)


        ['header' => 'OPERACIONES COMERCIALES', 'can' => 'ver_operaciones'],
        // 2. PUNTO DE VENTA (Destacado por icono, no por etiquetas)
        [
            'text'    => 'Punto de Venta (POS)',
            'url'     => 'ventas/create',
            'can'  => 'ventas.crear',
            'icon'    => 'fas fa-cash-register',
            'icon_color' => 'cyan',
            'active'  => ['pos*'],
        ],

        // 3. VENTAS Y CAJA (Agrupado para ahorrar espacio vertical)
        [
            'text'    => 'Ventas y Distribución', // Nombre sugerido más completo
            'icon'    => 'fas fa-coins',
            'submenu' => [
                [
                    'text'   => 'Caja Actual',
                    'url'    => 'cajas',
                    'can'    => 'cajas.ver',
                    'icon'   => 'fas fa-wallet',
                    'active' => ['cajas*'],
                ],
                [
                    'text' => 'Historial Ventas',
                    'url'  => 'ventas',
                    'can'  => 'ventas.ver',
                    'icon' => 'fas fa-file-invoice-dollar',
                ],
                // --- AQUÍ AGREGAMOS LAS GUÍAS ---
                [
                    'text'   => 'Guías de Remisión',
                    'url'    => 'guias',
                    'can' => 'guias.ver', // Descomenta cuando tengas el permiso
                    'icon'   => 'fas fa-shipping-fast', // Ícono de transporte/envío
                    'active' => ['guias*'],
                ],
                // --------------------------------
                [
                    'text' => 'Directorio de Clientes',
                    'url'  => 'clientes',
                    'can'  => 'clientes.ver',
                    'icon' => 'fas fa-users',
                ],
            ],
        ],

        // 3.5 

        ['header' => 'FACTURACIÓN ELECTRÓNICA (Sunat)', 'can' => 'ver_sunat'],
        [
            'text'    => 'Control SUNAT', // Título del menú desplegable
            'icon'    => 'fas fa-university', // Ícono de institución/gobierno
            'submenu' => [
                // 1. MONITOR DE ENVÍOS
                [
                    'text'       => 'Monitor de Envíos',
                    'url'        => 'facturacion/pendientes',
                    'can'     => 'sunat.monitor',
                    'icon'       => 'fas fa-satellite-dish',
                    'icon_color' => 'orange', // Se mantiene la alerta naranja
                ],

                // 2. REPOSITORIO XML / CDR / PDF
                [
                    'text'        => 'Archivos y Auditoría',
                    'url'         => 'facturacion/comprobantes',
                    'can'         => 'sunat.archivos',
                    'icon'        => 'fas fa-file-archive',
                    'label'       => 'XML/CDR',
                    'label_color' => 'info',
                ],
            ],
        ],

        // 3.5 - 2. CONTABILIDAD / SIRE (Lo que hablamos antes para el contador)


        ['header' => 'ADMINISTRACIÓN Y GESTIÓN', 'can' => 'ver_gestion'],
        [
            'text' => 'Inventario',
            'icon' => 'fas fa-boxes',
            'submenu' => [
                [
                    'text' => 'Medicamentos (Local)', // El que ya tienes (Filtrado por sucursal)
                    'url'  => 'inventario/medicamentos',
                    'can'  => 'medicamentos.ver',
                    'icon' => 'fas fa-pills',
                ],
                [
                    'text' => 'Categorías',
                    'url'  => 'inventario/categorias',
                    'can'  => 'categorias.ver',
                    'icon' => 'fas fa-tags',
                ],
                // --- NUEVO ÍTEM ---
                [
                    'text' => 'Catálogo General', // Nombre claro: son TODOS los productos
                    'url'  => 'inventario/medicamentos-general',
                    'can'    => 'medicamentos.global',
                    'icon' => 'fas fa-globe-americas', // Ícono global
                    'active' => ['inventario/medicamentos-general*'],
                ],
            ],
        ],

        [
            'text' => 'Logística / Compras',
            'icon' => 'fas fa-truck-loading',
            'submenu' => [
                [
                    'text' => 'Nueva Compra',
                    'url'  => 'compras/create',
                    'can'  => 'compras.crear',
                    'icon' => 'fas fa-plus',
                ],
                [
                    'text' => 'Historial Compras',
                    'url'  => 'compras',
                    'can'  => 'compras.ver',
                    'icon' => 'fas fa-history',
                ],
                [
                    'text' => 'Proveedores',
                    'url'  => 'proveedores',
                    'can'  => 'proveedores.ver',
                    'icon' => 'fas fa-people-carry',
                ],
            ],
        ],

        // 6. REPORTES
        [
            'text'    => 'Reportes',
            'icon'    => 'fas fa-chart-pie',
            'submenu' => [
                [
                    'text' => 'Ventas',
                    'icon' => 'fas fa-cash-register',
                    'can'  => 'reportes.ventas',
                    'submenu' => [
                        [
                            'text' => 'Ventas del Día',
                            'url' => 'reportes/ventas-dia',
                            'icon' => 'far fa-circle'


                        ],
                        [
                            'text' => 'Historial de ventas',
                            'url' => 'reportes/ventas-historial',
                            'icon' => 'far fa-circle'
                        ],
                        ['text' => 'Ventas Anuladas',   'url' => 'reportes/ventas-anuladas', 'icon' => 'far fa-circle text-danger'],
                    ],
                ],
                [
                    'text' => 'Estado de medicamento',
                    'icon' => 'fas fa-medkit',
                    'can'  => 'reportes.inventario',
                    'submenu' => [
                        ['text' => 'Lotes por Vencer',  'url' => 'reportes/vencimientos', 'icon' => 'far fa-circle text-danger'],
                        ['text' => 'Stock Bajo/Reponer', 'url' => 'reportes/stock-bajo',   'icon' => 'far fa-circle text-warning'],
                    ],
                ],
            ],
        ],
        // 7. CONFIGURACIÓN (Seguridad y Parámetros juntos)
        [
            'text' => 'Configuración',
            'icon' => 'fas fa-cogs',
            'submenu' => [
                [
                    'text' => 'Usuarios y Roles',
                    'url'  => 'seguridad/usuarios',
                    'can'  => 'usuarios.ver',
                    'icon' => 'fas fa-users-cog',
                ],
                [
                    'text' => 'Roles y permisos',
                    'url'  => 'seguridad/roles',
                    'can'  => 'roles.ver',
                    'icon' => 'fas fa-user-lock',
                ],
                [
                    'text' => 'Datos de Sucursales',
                    'url'  => 'configuracion/sucursales',
                    'can'  => 'sucursales.ver',
                    'icon' => 'fas fa-store',
                ],

                [
                    'text' => 'Empresa',
                    'url'  => 'configuracion/general',
                    'can'  => 'config.ver',
                    'icon' => 'fas fa-landmark',
                ],
            ],
        ],

    ],
    /*
    |--------------------------------------------------------------------------
    | Menu Filters
    |--------------------------------------------------------------------------
    */

    'filters' => [
        JeroenNoten\LaravelAdminLte\Menu\Filters\GateFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\HrefFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\SearchFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ActiveFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ClassesFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\LangFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\DataFilter::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugins Initialization
    |--------------------------------------------------------------------------
    */

    'plugins' => [
        'Datatables' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css',
                ],
            ],
        ],
        'Select2' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.css',
                ],
            ],
        ],
        'Chartjs' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.0/Chart.bundle.min.js',
                ],
            ],
        ],
        'Sweetalert2' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.jsdelivr.net/npm/sweetalert2@11',
                ],
            ],
        ],
        'Pace' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/themes/blue/pace-theme-center-radar.min.css',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/pace.min.js',
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | IFrame
    |--------------------------------------------------------------------------
    */

    'iframe' => [
        'default_tab' => [
            'url' => null,
            'title' => null,
        ],
        'buttons' => [
            'close' => true,
            'close_all' => true,
            'close_all_other' => true,
            'scroll_left' => true,
            'scroll_right' => true,
            'fullscreen' => true,
        ],
        'options' => [
            'loading_screen' => 1000,
            'auto_show_new_tab' => true,
            'use_navbar_items' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Livewire
    |--------------------------------------------------------------------------
    */

    'livewire' => false,
];
