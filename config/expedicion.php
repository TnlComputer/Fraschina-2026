<?php

return [

  /*
  | Primer número de pedido de la Central de Pedidos. Permite continuar la
  | numeración del sistema anterior (los pedidos nuevos usan max(numero)+1).
  */
  'numero_inicial' => (int) env('EXPEDICION_NUMERO_INICIAL', 1),

  /*
  | Nombre de la planilla que se envía al molino (antes "fax de pedido").
  */
  'nombre_planilla' => env('EXPEDICION_NOMBRE_PLANILLA', 'Orden de Pedido'),

  /*
  | Remitente de los mails de pedidos y consultas a molinos.
  */
  'mail_remitente' => env('EXPEDICION_MAIL_REMITENTE', 'pedidos@example.com'),
  'mail_remitente_nombre' => env('EXPEDICION_MAIL_REMITENTE_NOMBRE', 'Fraschina - Control de Pedidos'),

  /*
  | Disco y carpetas donde se archivan las planillas enviadas y el respaldo diario.
  */
  'disco' => env('EXPEDICION_DISCO', 'local'),
  'carpeta_planillas' => 'expedicion/planillas',
  'carpeta_respaldos' => 'expedicion/respaldos',

  /*
  | Días de respaldos diarios que se conservan.
  */
  'respaldos_dias' => (int) env('EXPEDICION_RESPALDOS_DIAS', 30),

  /*
  | Horarios de las consultas automáticas a molinos (formato HH:MM).
  */
  'consulta_manana' => env('EXPEDICION_CONSULTA_MANANA', '08:00'),
  'consulta_tarde' => env('EXPEDICION_CONSULTA_TARDE', '16:00'),

];
