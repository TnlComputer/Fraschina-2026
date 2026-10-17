<?php

/*
| Actualización diaria desde el sistema anterior (comando legado:sincronizar).
|
| La base del sistema anterior tiene que estar en el mismo servidor de base de datos que la de la app
| (en el hosting lo están). Se lee sin modificarla.
*/
return [

  // Nombre de la base del sistema anterior (en el hosting: fraschin_asrl)
  'origen' => env('LEGADO_DB'),

  // Mientras convivan los dos sistemas, los clientes de Distribución se dan de alta solo en el sistema anterior
  // (su número es el código con el que los llaman). Decisión del 17/10/2026.
  'clientes_solo_en_anterior' => (bool) env('LEGADO_CLIENTES_SOLO_EN_ANTERIOR', true),

  // Correr todas las noches (si hay origen configurado)
  'activo' => (bool) env('LEGADO_SINCRONIZAR', true),
  'hora' => env('LEGADO_HORA', '02:00'),

  // Scripts de conversión (en orden). Los de Expedición (00-4, 01, 02) no: esos datos vienen de las planillas.
  'scripts' => [
    '00-0_aux.sql',
    '00-1_aux1-agro-prodcutosCDA.sql',
    '00-2_distribucions-1-solo.sql',
    '00-2_distribucions-1b-eliminados.sql',
    '00-2_distribucions-2productos-solo.sql',
    '00-2_distribucions-3resto.sql',
    '00-2_distribucions-4facturacion.sql',
    '00-2_transporte-0representaciones.sql',
    '00-2_transporte-1representaciones-productos.sql',
    '00-3_molino-proveedores.sql',
  ],

  // Tablas de trabajo (dentro de la base de la app)
  'prefijo_hoy' => 'leg_hoy_',     // conversión de esta noche
  'prefijo_base' => 'leg_base_',   // conversión de la noche anterior (contra la que se compara)

  // Columnas que no se comparan (las pone la conversión o la app)
  'ignorar' => ['created_at', 'updated_at', 'deleted_at'],
];
