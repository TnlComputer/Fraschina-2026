<?php

namespace App\Providers;

// use App\Services\EnLetras;
// use App\Services\EnLetrasService;
use Illuminate\Pagination\Paginator;
use App\Services\Auditoria;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
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

    // Accesos: ingresos, salidas e intentos fallidos
    Event::listen(Login::class, function (Login $e) {
      if (isset($e->user->is_active) && !$e->user->is_active) {
        // El login lo cierra enseguida (usuario desactivado): se registra como rechazado, sin la salida
        request()->attributes->set('auditoria_ingreso_rechazado', true);
        app(Auditoria::class)->acceso('ingreso_rechazado', $e->user, "Ingreso rechazado: {$e->user->name} está desactivado");
        return;
      }
      app(Auditoria::class)->acceso('ingreso', $e->user, "Ingreso de {$e->user->name}");
    });
    Event::listen(Logout::class, function (Logout $e) {
      if ($e->user && !request()->attributes->get('auditoria_ingreso_rechazado')) {
        app(Auditoria::class)->acceso('salida', $e->user, "Salida de {$e->user->name}");
      }
    });
    Event::listen(Failed::class, function (Failed $e) {
      $email = (string) ($e->credentials['email'] ?? '');
      app(Auditoria::class)->acceso(
        'ingreso_fallido',
        $e->user,
        $e->user ? "Contraseña incorrecta para {$e->user->name}" : "Intento de ingreso con un mail inexistente",
        ['email' => [null, $email]]
      );
    });
  }
}
