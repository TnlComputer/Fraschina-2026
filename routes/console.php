<?php

use Illuminate\Support\Facades\Schedule;

/*
| Tareas programadas. En el servidor hace falta un cron que corra cada minuto:
|   * * * * * cd /ruta/a/app-nueva && php artisan schedule:run >> /dev/null 2>&1
*/

// Central de Pedidos: respaldo diario en Excel (por si no hay acceso al sistema)
Schedule::command('expedicion:respaldo')->dailyAt('06:30')->withoutOverlapping();

// Consultas a molinos: mañana (entregas del día) y tarde (cargas del día siguiente).
// Horarios, días y si están activas se definen en Herramientas → Configuración.
foreach (['manana' => 'expedicion.consulta_manana', 'tarde' => 'expedicion.consulta_tarde'] as $turno => $hora) {
  Schedule::command("expedicion:consultas $turno")
    ->days(config('expedicion.consulta_dias') ?: [1, 2, 3, 4, 5, 6])
    ->at(config($hora))
    ->when(fn() => (bool) config('expedicion.consultas_activas'));
}

// Actualización desde el sistema anterior (altas, modificaciones y bajas), todas las noches
Schedule::command('legado:sincronizar')
  ->dailyAt(config('legado.hora'))
  ->when(fn() => config('legado.activo') && config('legado.origen'))
  ->withoutOverlapping(120);
