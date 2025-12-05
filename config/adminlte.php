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
    'dashboard_url' => 'home',
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
        [
            'text' => 'Dashboard',
            'url'  => 'dashboard',
            'icon' => 'fas fa-tachometer-alt',
        ],

        ['header' => 'OPERACIONES COMERCIALES'],

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
            'text' => 'Ventas y Caja',
            'icon' => 'fas fa-coins',
            'submenu' => [
                [
                    'text' => 'Caja Actual',
                    'url'  => 'cajas',
                    'can'  => 'cajas.ver',
                    'icon' => 'fas fa-wallet',
                    'active' => ['cajas*'],
                ],
                [
                    'text' => 'Historial Ventas',
                    'url'  => 'ventas',
                    'can'  => 'ventas.ver',
                    'icon' => 'fas fa-file-invoice-dollar',
                    'can'  => 'ventas.ver',
                ],
                [
                    'text' => 'Directorio de Clientes',
                    'url'  => 'clientes',
                    'can'  => 'clientes.ver',
                    'icon' => 'fas fa-users',
                ],
            ],
        ],

        // 4. INVENTARIO FARMACÉUTICO (Todo lo de productos aquí)
        [
            'text' => 'Inventario',
            'icon' => 'fas fa-boxes',
            'submenu' => [
                [
                    'text' => 'Medicamentos',
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
                [
                    'text' => 'Lotes y Vencimientos',
                    'url'  => 'inventario/lotes',
                    'can'  => 'vencimiento.ver',
                    'icon' => 'fas fa-calendar-alt',
                ],
                [
                    'text' => 'Ajustes de Stock',
                    'url'  => 'inventario/ajustes',
                    'can'  => 'stock.ajustar',
                    'icon' => 'fas fa-sliders-h',
                ],
            ],
        ],

        ['header' => 'ADMINISTRACIÓN Y GESTIÓN'],

        // 5. LOGÍSTICA (Compras y Proveedores juntos)
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

        // 6. REPORTES (Limpio en un solo bloque)
        [
            'text' => 'Reportes',
            'icon' => 'fas fa-chart-bar',
            'submenu' => [
                ['text' => 'Ventas del Día', 'url' => '#', 'icon' => 'far fa-circle'],
                ['text' => 'Productos Top', 'url' => '#', 'icon' => 'far fa-circle'],
                ['text' => 'Rentabilidad', 'url' => '#', 'icon' => 'far fa-circle'],
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
                    'text' => 'Datos de Empresa',
                    'url'  => 'configuracion/sucursales',
                    'can'  => 'sucursales.ver',
                    'icon' => 'fas fa-building',
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
