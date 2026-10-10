<?php

use Illuminate\Support\Facades\Schedule;

/*
| Tareas programadas. En el servidor hace falta un cron que corra cada minuto:
|   * * * * * cd /ruta/a/app-nueva && php artisan schedule:run >> /dev/null 2>&1
*/

// Central de Pedidos: respaldo diario en Excel (por si no hay acceso al sistema)
Schedule::command('expedicion:respaldo')->dailyAt('06:30')->withoutOverlapping();

// Consultas a molinos: mañana (entregas del día) y tarde (cargas del día siguiente), de lunes a sábado
Schedule::command('expedicion:consultas manana')->days([1, 2, 3, 4, 5, 6])->at(config('expedicion.consulta_manana'));
Schedule::command('expedicion:consultas tarde')->days([1, 2, 3, 4, 5, 6])->at(config('expedicion.consulta_tarde'));
