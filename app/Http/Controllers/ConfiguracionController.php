<?php

namespace App\Http\Controllers;

use App\Models\ExpPedido;
use App\Services\Configuraciones;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Herramientas → Configuración: parámetros de la app editables por el Administrador y el Supervisor
 * (permiso_10). Cada cambio queda en la auditoría.
 */
class ConfiguracionController extends Controller
{
  public function __construct(private Configuraciones $configuraciones)
  {
  }

  public function index()
  {
    return view('pages.Tools.Configuracion.index', [
      'c' => fn(string $clave) => $this->configuraciones->valor($clave),
      'modos' => Configuraciones::MODOS_CORREO,
      'dias' => Configuraciones::DIAS,
      'hayPedidos' => $this->hayPedidos(),
      'correoServidor' => config('mail.mailer_env') === 'smtp' ? config('mail.mailers.smtp.host') : null,
    ]);
  }

  public function update(Request $request)
  {
    $datos = $request->validate([
      'mail_modo' => ['required', Rule::in(array_keys(Configuraciones::MODOS_CORREO))],
      'mail_casilla_prueba' => ['nullable', 'email', 'max:150', Rule::requiredIf($request->mail_modo === Configuraciones::PRUEBA)],
      'mail_remitente' => 'required|email|max:150',
      'mail_remitente_nombre' => 'required|string|max:100',
      'consultas_activas' => 'nullable|boolean',
      'consulta_manana' => 'required|date_format:H:i',
      'consulta_tarde' => 'required|date_format:H:i',
      'consulta_dias' => ['array', Rule::requiredIf($request->boolean('consultas_activas'))],
      'consulta_dias.*' => 'integer|between:0,6',
      'numero_inicial' => 'nullable|integer|min:1|max:99999999',
      'nombre_planilla' => 'required|string|max:100',
      'respaldos_dias' => 'required|integer|between:1,365',
    ], [
      'mail_casilla_prueba.required' => 'Indique la casilla de prueba a la que llegarán todos los correos.',
      'consulta_dias.required' => 'Elija al menos un día para las consultas.',
    ], [
      'mail_casilla_prueba' => 'casilla de prueba', 'mail_remitente' => 'correo del remitente', 'mail_remitente_nombre' => 'nombre del remitente',
      'consulta_manana' => 'hora de la mañana', 'consulta_tarde' => 'hora de la tarde', 'numero_inicial' => 'número inicial',
      'nombre_planilla' => 'nombre de la planilla', 'respaldos_dias' => 'días de respaldo',
    ]);

    $valores = [
      'mail.modo' => $datos['mail_modo'],
      'mail.casilla_prueba' => $datos['mail_casilla_prueba'] ?? null,
      'expedicion.mail_remitente' => $datos['mail_remitente'],
      'expedicion.mail_remitente_nombre' => $datos['mail_remitente_nombre'],
      'expedicion.consultas_activas' => $request->boolean('consultas_activas'),
      'expedicion.consulta_manana' => $datos['consulta_manana'],
      'expedicion.consulta_tarde' => $datos['consulta_tarde'],
      'expedicion.consulta_dias' => array_values(array_unique(array_map('intval', $datos['consulta_dias'] ?? []))),
      'expedicion.nombre_planilla' => $datos['nombre_planilla'],
      'expedicion.respaldos_dias' => $datos['respaldos_dias'],
    ];
    // La numeración solo se define antes del primer pedido; después la siguen los propios pedidos
    if (!$this->hayPedidos() && isset($datos['numero_inicial'])) {
      $valores['expedicion.numero_inicial'] = $datos['numero_inicial'];
    }

    $cambiadas = $this->configuraciones->guardar($valores);

    return redirect()->route('configuracion.index')->with('success', $cambiadas
      ? 'Configuración guardada (' . count($cambiadas) . ' cambio' . (count($cambiadas) > 1 ? 's' : '') . ').'
      : 'No hubo cambios.');
  }

  private function hayPedidos(): bool
  {
    return ExpPedido::whereNotNull('numero')->exists();
  }
}
