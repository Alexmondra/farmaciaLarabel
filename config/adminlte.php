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
    'classes_sidebar' => 'sidebar-dark-teal elevation-4', // Sidebar oscura con hover teal
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
    | Menu Items
    |--------------------------------------------------------------------------
    */

    'menu' => [
        // Navbar items:
        [
            'type' => 'navbar-search',
            'text' => 'Buscar medicamento, lote o venta...',
            'topnav_right' => true,
        ],
        [
            'type' => 'fullscreen-widget',
            'topnav_right' => true,
        ],

        // Sidebar items:
        [
            'type' => 'sidebar-menu-search',
            'text' => 'Buscar módulo...',
        ],

        ['header' => 'PRINCIPAL'],
        [
            'text' => 'Dashboard',
            'url'  => 'dashboard', // Asumiendo que tienes una ruta home
            'icon' => 'fas fa-tachometer-alt',
            'label'       => 'Resumen',
            'label_color' => 'success',
        ],

        // =============================================
        // MÓDULO DE CAJA Y MOSTRADOR (Lo más usado)
        // =============================================
        ['header' => 'MOSTRADOR Y CAJA'],

        [
            'text'    => 'Punto de Venta (POS)',
            'url'     => '#', // Futura ruta
            'icon'    => 'fas fa-cash-register',
            'icon_color' => 'cyan',
            'active'  => ['pos*'],
        ],
        [
            'text' => 'Operaciones de Caja',
            'icon' => 'fas fa-wallet',
            'submenu' => [
                [
                    'text' => 'Apertura / Cierre',
                    'url'  => 'cajas',
                    'icon' => 'fas fa-door-open',
                    'active' => ['cajas*'],
                ],
                [
                    'text' => 'Movimientos de Efectivo',
                    'url'  => '#',
                    'icon' => 'fas fa-money-bill-wave',
                ],
            ],
        ],
        [
            'text'    => 'Historial de Ventas',
            'url'     => 'ventas',
            'icon'    => 'fas fa-receipt',
            'can'     => 'ventas.ver',
        ],

        // =============================================
        // MÓDULO FARMACÉUTICO (El corazón del negocio)
        // =============================================
        ['header' => 'GESTIÓN FARMACÉUTICA'],

        [
            'text' => 'Catálogo Productos',
            'icon' => 'fas fa-prescription-bottle-alt',
            'submenu' => [
                [
                    'text' => 'Medicamentos',
                    'url'  => 'inventario/medicamentos',
                    'icon' => 'fas fa-pills',
                    'can'  => 'medicamentos.ver',
                ],
                [
                    'text' => 'Categorías / Familias',
                    'url'  => 'inventario/categorias',
                    'icon' => 'fas fa-tags',
                    'can'  => 'categorias.ver',
                ],
                [
                    'text' => 'Laboratorios',
                    'url'  => '#',
                    'icon' => 'fas fa-flask',
                ],
            ],
        ],
        [
            'text' => 'Control de Stock',
            'icon' => 'fas fa-cubes',
            'submenu' => [
                [
                    'text' => 'Kardex Físico',
                    'url'  => '#',
                    'icon' => 'fas fa-clipboard-list',
                ],
                [
                    'text' => 'Lotes y Vencimientos',
                    'url'  => '#',
                    'icon' => 'fas fa-calendar-times',
                    'label'       => 'Alerta',
                    'label_color' => 'danger', // Esto llama la atención visualmente
                ],
                [
                    'text' => 'Ajustes de Inventario',
                    'url'  => '#',
                    'icon' => 'fas fa-sliders-h',
                ],
            ],
        ],

        // =============================================
        // MÓDULO DE COMPRAS
        // =============================================
        ['header' => 'ABASTECIMIENTO'],

        [
            'text' => 'Gestión de Compras',
            'icon' => 'fas fa-shopping-cart',
            'submenu' => [
                [
                    'text' => 'Nueva Compra',
                    'url'  => 'compras',
                    'icon' => 'fas fa-plus-circle',
                ],
                [
                    'text' => 'Historial Compras',
                    'url'  => 'listadoCompras',
                    'icon' => 'fas fa-history',
                ],
                [
                    'text' => 'Proveedores',
                    'url'  => 'proveedores',
                    'icon' => 'fas fa-truck',
                ],
            ],
        ],

        // =============================================
        // REPORTES E INTELIGENCIA
        // =============================================
        ['header' => 'REPORTES'],

        [
            'text' => 'Reportes Gerenciales',
            'icon' => 'fas fa-chart-pie',
            'submenu' => [
                ['text' => 'Reporte del Día', 'url' => '#', 'icon' => 'far fa-circle'],
                ['text' => 'Productos más vendidos', 'url' => '#', 'icon' => 'far fa-circle'],
                ['text' => 'Utilidad / Ganancia', 'url' => '#', 'icon' => 'far fa-circle'],
            ],
        ],

        // =============================================
        // ADMINISTRACIÓN
        // =============================================
        ['header' => 'CONFIGURACIÓN'],

        [
            'text' => 'Acceso y Seguridad',
            'icon' => 'fas fa-lock',
            'can'  => 'usuarios.ver',
            'submenu' => [
                [
                    'text' => 'Usuarios del Sistema',
                    'url'  => 'seguridad/usuarios',
                    'icon' => 'fas fa-users',
                    'can'  => 'usuarios.ver',
                ],
                [
                    'text' => 'Roles y Permisos',
                    'url'  => 'seguridad/roles',
                    'icon' => 'fas fa-user-shield',
                    'can'  => 'roles.ver',
                ],
            ],
        ],
        [
            'text' => 'Parametros Generales',
            'icon' => 'fas fa-cogs',
            'submenu' => [
                [
                    'text' => 'Datos de Farmacia',
                    'url'  => 'configuracion/sucursales',
                    'icon' => 'fas fa-clinic-medical',
                    'can'  => 'sucursales.ver',
                ],
                ['text' => 'Impresoras / Tickets', 'url' => '#', 'icon' => 'fas fa-print'],
            ],
        ],

        ['header' => 'SOPORTE'],
        [
            'text' => 'Blog / Novedades',
            'url'  => 'admin/blog',
            'icon' => 'fas fa-info-circle',
            'can'  => 'manage-blog',
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
