<?php

namespace App\Providers;

// use App\Services\EnLetras;
// use App\Services\EnLetrasService;
use Illuminate\Pagination\Paginator;
use App\Services\Auditoria;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
  /**
   * Register any application services.
   */
  public function register(): void
  {
    //
  }

  /**
   * Bootstrap any application services.
   */
  public function boot(): void
  {
    Paginator::useBootstrap();

    // permiso_99 (Super Usuario) pasa todos los chequeos de permisos
    Gate::before(fn($user) => $user->checkPermissionTo('permiso_99') ? true : null);

    // Log de operaciones: toda alta, modificación, eliminación y restauración de cualquier modelo
    foreach (['created', 'updated', 'deleted', 'restored'] as $evento) {
      Event::listen("eloquent.{$evento}: *", function (string $nombre, array $datos) use ($evento) {
        try {
          app(Auditoria::class)->modelo($datos[0], $evento);
        } catch (\Throwable $e) {
          // El log nunca debe impedir la operación del usuario
          report($e);
        }
      });
    }
  }
}
