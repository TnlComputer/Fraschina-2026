<?php

// use Illuminate\Support\Facades\Request;

return [

  /*
    |--------------------------------------------------------------------------
    | Title
    |--------------------------------------------------------------------------
    |
    | Here you can change the default title of your admin panel.
    |
    | For detailed instructions you can look the title section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

  'title' => 'Fraschina SRL',
  'title_prefix' => '',
  'title_postfix' => ' | Fraschina',

  /*
    |--------------------------------------------------------------------------
    | Favicon
    |--------------------------------------------------------------------------
    |
    | Here you can activate the favicon.
    |
    | For detailed instructions you can look the favicon section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

  'use_ico_only' => false,
  'use_full_favicon' => false,

  /*
    |--------------------------------------------------------------------------
    | Google Fonts
    |--------------------------------------------------------------------------
    |
    | Here you can allow or not the use of external google fonts. Disabling the
    | google fonts may be useful if your admin panel internet access is
    | restricted somehow.
    |
    | For detailed instructions you can look the google fonts section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

  'google_fonts' => [
    'allowed' => true,
  ],

  /*
    |--------------------------------------------------------------------------
    | Admin Panel Logo
    |--------------------------------------------------------------------------
    |
    | Here you can change the logo of your admin panel.
    |
    | For detailed instructions you can look the logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

  'logo' => '<b>Fraschina</b> S.R.L.',
  'logo_img' => 'vendor/adminlte/dist/img/FraschinaLogo.png',
  'logo_img_class' => 'brand-image img-circle elevation-3',
  'logo_img_xl' => null,
  'logo_img_xl_class' => 'brand-image-xs',
  'logo_img_alt' => 'Fraschina',

  /*
    |--------------------------------------------------------------------------
    | Authentication Logo
    |--------------------------------------------------------------------------
    |
    | Here you can setup an alternative logo to use on your login and register
    | screens. When disabled, the admin panel logo will be used instead.
    |
    | For detailed instructions you can look the auth logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

  'auth_logo' => [
    'enabled' => false,
    'img' => [
      'path' => 'vendor/adminlte/dist/img/FraschinaLogo.png',
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
    |
    | Here you can change the preloader animation configuration. Currently, two
    | modes are supported: 'fullscreen' for a fullscreen preloader animation
    | and 'cwrapper' to attach the preloader animation into the content-wrapper
    | element and avoid overlapping it with the sidebars and the top navbar.
    |
    | For detailed instructions you can look the preloader section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

  'preloader' => [
    'enabled' => true,
    'mode' => 'fullscreen',
    'img' => [
      'path' => 'vendor/adminlte/dist/img/FraschinaLogo.png',
      'alt' => 'Fraschina Preloader Image',
      'effect' => 'animation__shake',
      'width' => 60,
      'height' => 60,
    ],
  ],

  /*
    |--------------------------------------------------------------------------
    | User Menu
    |--------------------------------------------------------------------------
    |
    | Here you can activate and change the user menu.
    |
    | For detailed instructions you can look the user menu section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

  'usermenu_enabled' => true,
  'usermenu_header' => false,
  'usermenu_header_class' => 'bg-primary',
  'usermenu_image' => false,
  'usermenu_desc' => false,
  'usermenu_profile_url' => false,

  /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    |
    | Here we change the layout of your admin panel.
    |
    | For detailed instructions you can look the layout section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

  'layout_topnav' => null,
  'layout_boxed' => null,
  'layout_fixed_sidebar' => true,
  'layout_fixed_navbar' => true,
  'layout_fixed_footer' => null,
  'layout_dark_mode' => null,

  /*
    |--------------------------------------------------------------------------
    | Authentication Views Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the authentication views.
    |
    | For detailed instructions you can look the auth classes section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

  'classes_auth_card' => 'card-outline card-primary',
  'classes_auth_header' => '',
  'classes_auth_body' => '',
  'classes_auth_footer' => '',
  'classes_auth_icon' => '',
  'classes_auth_btn' => 'btn-flat btn-primary',

  /*
    |--------------------------------------------------------------------------
    | Admin Panel Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the admin panel.
    |
    | For detailed instructions you can look the admin panel classes here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

  'classes_body' => '',
  'classes_brand' => '',
  'classes_brand_text' => '',
  'classes_content_wrapper' => '',
  'classes_content_header' => '',
  'classes_content' => '',
  'classes_sidebar' => 'sidebar-dark-primary elevation-4',
  'classes_sidebar_nav' => '',
  'classes_topnav' => 'navbar-white navbar-light',
  'classes_topnav_nav' => 'navbar-expand',
  'classes_topnav_container' => 'container',

  /*
    |--------------------------------------------------------------------------
    | Sidebar
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar of the admin panel.
    |
    | For detailed instructions you can look the sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

  'sidebar_mini' => 'lg',
  'sidebar_collapse' => false,
  'sidebar_collapse_auto_size' => false,
  'sidebar_collapse_remember' => true,
  'sidebar_collapse_remember_no_transition' => true,
  'sidebar_scrollbar_theme' => 'os-theme-light',
  'sidebar_scrollbar_auto_hide' => 'l',
  'sidebar_nav_accordion' => true,
  'sidebar_nav_animation_speed' => 300,

  /*
    |--------------------------------------------------------------------------
    | Control Sidebar (Right Sidebar)
    |--------------------------------------------------------------------------
    |
    | Here we can modify the right sidebar aka control sidebar of the admin panel.
    |
    | For detailed instructions you can look the right sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
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
    |
    | Here we can modify the url settings of the admin panel.
    |
    | For detailed instructions you can look the urls section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

  'use_route_url' => false,
  'dashboard_url' => '/dashboard',
  'logout_url' => 'logout',
  'login_url' => 'login',
  'register_url' => false,
  'password_reset_url' => 'password/reset',
  'password_email_url' => 'password/email',
  'profile_url' => false,
  'disable_darkmode_routes' => false,

  /*
    |--------------------------------------------------------------------------
    | Laravel Asset Bundling
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Laravel Asset Bundling option for the admin panel.
    | Currently, the next modes are supported: 'mix', 'vite' and 'vite_js_only'.
    | When using 'vite_js_only', it's expected that your CSS is imported using
    | JavaScript. Typically, in your application's 'resources/js/app.js' file.
    | If you are not using any of these, leave it as 'false'.
    |
    | For detailed instructions you can look the asset bundling section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

  'laravel_asset_bundling' => false,
  'laravel_css_path' => 'css/app.css',
  'laravel_js_path' => 'js/app.js',

  /*
    |--------------------------------------------------------------------------
    | Menu Items
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar/top navigation of the admin panel.
    |
    | For detailed instructions you can look here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

  'menu' => [
    // Navbar items:
    // [
    //   'type' => 'navbar-search',
    //   'text' => 'search',
    //   'topnav_right' => true,
    // ],
    [
      'type' => 'fullscreen-widget',
      'topnav_right' => true,
    ],
    [
      'text' => 'Representación',
      'url' => 'representacion',
      'icon' => 'fas fa-fw fa-briefcase',
      'can' => ['permiso_99', 'permiso_1'],

    ],
    [
      'text' => 'Distribuciones',
      'icon' => 'fas fa-fw fa-truck',
      'can' => ['permiso_99', 'permiso_2'],
      'submenu' => [
        [
          'text' => 'Distribución',
          'url' => 'distribucion',
          'icon' => 'fas fa-fw fa-truck',
          'can' => ['permiso_99', 'permiso_2'],
        ],
        [
          'text' => 'Agenda',
          'url' => 'distribucion_agenda',
          'icon' => 'fas fa-fw fa-truck',
          // 'icon' => 'nav-icon fas fa-calendar-alt',
          'can' => ['permiso_99', 'permiso_2'],
        ],
        [
          'text' => 'Reparto',
          'url' => 'distribucion_reparto',
          'icon' => 'fas fa-fw fa-truck',
          'can' => ['permiso_99', 'permiso_2'],
        ],
        [
          'text' => 'Stock',
          'url' => 'distribucion_stock',
          'icon' => 'fas fa-fw fa-truck',
          'can' => ['permiso_99', 'permiso_2'],
        ],
      ],
    ],
    [
      'text' => 'Molino',
      'url' => 'molino',
      'icon' => 'fas fa-fw fa-industry',
      'can' => ['permiso_99', 'permiso_3'],
    ],
    [
      'text' => 'Agro/AgroInd',
      'url' => 'agro',
      'icon' => 'fas fa-fw fa-leaf',
      'can' => ['permiso_99', 'permiso_4'],
    ],
    [
      'text' => 'Proveedor',
      'url' => 'proveedor',
      'icon' => 'fas fa-fw fa-cogs',
      'can' => ['permiso_99', 'permiso_5'],
    ],
    [
      'text' => 'Transporte',
      'url' => 'transporte',
      'icon' => 'fas fa-fw fa-bus',
      'can' => ['permiso_99', 'permiso_6'],
    ],
    [
      'text' => 'Expedición',
      'icon' => 'fas fa-fw fa-map-marked-alt',
      'can' => ['permiso_99', 'permiso_7'],
      'submenu' => [
        ['text' => 'Tablero', 'url' => 'expedicion', 'icon' => 'fas fa-fw fa-tachometer-alt'],
        ['text' => 'Pedidos', 'url' => 'expedicion_pedido', 'icon' => 'fas fa-fw fa-clipboard-list'],
        ['text' => 'Planificación semanal', 'url' => 'expedicion/planificacion', 'icon' => 'far fa-fw fa-calendar-alt'],
        ['text' => 'Control de entregas', 'url' => 'expedicion/control', 'icon' => 'fas fa-fw fa-truck'],
        ['text' => 'Diaria', 'url' => 'expedicion/diaria', 'icon' => 'fas fa-fw fa-history'],
        ['text' => 'Calidad (General)', 'url' => 'expedicion_general', 'icon' => 'fas fa-fw fa-flask'],
        [
          'text' => 'Informes',
          'icon' => 'fas fa-fw fa-chart-bar',
          'submenu' => [
            ['text' => 'Tolvas y bolsas', 'url' => 'expedicion/informes/tolvas'],
            ['text' => 'Certificados pendientes', 'url' => 'expedicion/informes/certificados'],
          ],
        ],
        [
          'text' => 'Configuración',
          'icon' => 'fas fa-fw fa-cogs',
          'submenu' => [
            ['text' => 'Clientes', 'url' => 'expedicion_cliente'],
            ['text' => 'Molinos', 'url' => 'expedicion_molino'],
            ['text' => 'Cliente-molino', 'url' => 'expedicion_MTC'],
            ['text' => 'Avisos', 'url' => 'expedicion/avisos'],
          ],
        ],
        ['text' => 'Fichas', 'url' => 'expedicion/fichas', 'icon' => 'fas fa-fw fa-folder-open'],
      ],
    ],
    [
      'text' => 'Agenda General',
      'url' => 'AgendaGral',
      'icon' => 'fas fa-fw fa-calendar',
      'can' => ['permiso_99', 'permiso_8'],

    ],
    // [
    //   'header' => 'ADMIN SETTINGS',
    //   'can' => ['permiso_99', 'permiso_9'],
    // ],
    [
      'text' => 'Tools',
      'icon' => 'fas fa-fw fa-tools',
      'can' => ['permiso_99', 'permiso_9'],
      // 'icon' => 'fas fa-fw fa-share',
      'submenu' => [
        [
          'text' => 'Usuarios',
          'icon' => 'fas fa-fw fa-users',
          'can' => ['permiso_99', 'permiso_9'],
          'submenu' => [
            [
              'text' => 'Usuario',
              'icon' => 'fas fa-fw fa-user',
              'url' => 'tools/usuarios',
            ],
            [
              'text' => 'Rol',
              'icon' => 'fas fa-fw fa-user-shield',
              'url' => 'tools/roles',
            ],
            [
              'text' => 'Permiso',
              'icon' => 'fas fa-fw fa-id-badge',
              'url' => 'tools/permissions',
              'can' => ['permiso_99'],
            ],
            [
              'text' => 'Auditoría',
              'icon' => 'fas fa-fw fa-clipboard-check',
              'url' => 'tools/auditoria',
              'can' => ['permiso_99'],
            ],
          ],
        ],
        [
          'text' => 'Ubicación',
          'icon' => 'fas fa-fw fa-location-pin',
          'can' => ['permiso_99', 'permiso_9'],
          'submenu' => [
            [
              'text' => 'Barrios',
              'icon' => 'fas fa-fw fa-building',
              'url' => 'tools/barrios',
            ],
            [
              'text' => 'Calles',
              'icon' => 'fas fa-fw fa-road',
              'url' => 'tools/calles',
            ],
            [
              'text' => 'Ciudades/Municipios',
              'icon' => 'fas fa-fw fa-city',
              'url' => 'tools/ciudades_municipios',
            ],
            [
              'text' => 'Localidades',
              'icon' => 'fas fa-fw fa-location-arrow',
              'url' => 'tools/localidades',
            ],
            [
              'text' => 'Zonas',
              'icon' => 'fas fa-fw fa-th-large',
              // 'icon' => 'fas fa-fw fa-layer-group',
              'url' => 'tools/zonas',
            ],
          ],
        ],
        [
          'text' => 'Auxiliares',
          'icon' => 'fas fa-fw fa-question-circle',
          'can' => ['permiso_99', 'permiso_9'],
          'submenu' => [
            [
              'text' => 'Acciones',
              'icon' => 'fas fa-fw fa-tasks',
              'url' => 'tools/acciones',
            ],
            [
              'text' => 'Areas',
              'icon' => 'fas fa-fw fa-layer-group',
              'url' => 'tools/areas',
            ],
            [
              'text' => 'Contacto Inicial',
              'icon' => 'fas fa-fw fa-phone',
              'url' => 'tools/contacto_inicial',
            ],
            [
              'text' => 'Cargos',
              'icon' => 'fas fa-fw fa-user-tie',
              'url' => 'tools/cargos',
            ],
            [
              'text' => 'Estados',
              'icon' => 'fas fa-fw fa-spinner',
              'url' => 'tools/estados',
            ],
            [
              'text' => 'Horarios',
              'icon' => 'fas fa-fw fa-clock',
              'url' => 'tools/horas',
            ],
            [
              'text' => 'Prioridades',
              // 'icon' => 'fas fa-fw fa-phone',
              'icon' => 'fas fa-fw fa-fire',
              'url' => 'tools/prioridades',
            ],
            [
              'text' => 'Rubros',
              'icon' => 'fas fa-fw fa-cogs',
              'url' => 'tools/rubros',
            ],
            [
              'text' => 'Tareas',
              'icon' => 'fas fa-fw fa-clipboard-list',
              'url' => 'tools/tareas',
            ],
            [
              'text' => 'Tipo Personas',
              'icon' => 'fas fa-fw fa-user',
              'url' => 'tools/tipo_persona',
            ],
            [
              'text' => 'Veraz',
              'icon' => 'fas fa-fw fa-check-circle',
              'url' => 'tools/veraz',
            ],
            [
              'text' => 'Exportar Tablas',
              'icon' => 'fas fa-fw fa-file-export',
              'url' => 'tools/export',
            ],
          ],
        ],
        [
          'text' => 'Modos',
          'icon' => 'fas fa-fw fa-sliders-h',
          'url' => 'tools/modos',
        ],
        [
          'text' => 'Familias',
          'icon' => 'fas fa-fw fa-briefcase',
          'url' => 'tools/familias',
        ],
        [
          'text' => 'Tamaños',
          'icon' => 'fas fa-fw fa-ruler-combined',
          'url' => 'tools/dimension',
          // 'url' => 'tools/tamanios',
        ],
      ],
    ],
    ['header' => 'account_settings'],
    [
      'text' => 'profile',
      'url' => 'profile',
      'icon' => 'fas fa-fw fa-user',
    ],
  ],

  /*
    |--------------------------------------------------------------------------
    | Menu Filters
    |--------------------------------------------------------------------------
    |
    | Here we can modify the menu filters of the admin panel.
    |
    | For detailed instructions you can look the menu filters section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
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
    |
    | Here we can modify the plugins used inside the admin panel.
    |
    | For detailed instructions you can look the plugins section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Plugins-Configuration
    |
    */

  'plugins' => [
    // Estilos propios de la app (public/css/fraschina.css), en todas las páginas
    'Fraschina' => [
      'active' => true,
      'files' => [
        [
          'type' => 'css',
          'asset' => true,
          'location' => 'css/fraschina.css?v=20261011g',
        ],
      ],
    ],
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
      'active' => false,
      'files' => [
        [
          'type' => 'js',
          'asset' => false,
          'location' => '//cdn.jsdelivr.net/npm/sweetalert2@8',
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
    |
    | Here we change the IFrame mode configuration. Note these changes will
    | only apply to the view that extends and enable the IFrame mode.
    |
    | For detailed instructions you can look the iframe mode section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/IFrame-Mode-Configuration
    |
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
    |
    | Here we can enable the Livewire support.
    |
    | For detailed instructions you can look the livewire here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

  'livewire' => false,
];
