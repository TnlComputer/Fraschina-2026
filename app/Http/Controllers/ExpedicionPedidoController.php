<?php

namespace App\Http\Controllers;

use App\Mail\PedidoMolinoMail;
use App\Models\ExpedicionCliente;
use App\Models\ExpedicionMolino;
use App\Models\ExpedicionMolinoTextoCliente;
use App\Models\ExpPedido;
use App\Services\CentralPedidos;
use App\Services\Notificador;
use App\Services\PlanillaPedido;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExpedicionPedidoController extends Controller
{
  public function __construct(
    private CentralPedidos $central,
    private PlanillaPedido $planilla,
  ) {}

  public function index(Request $request)
  {
    $request->validate([
      'desde' => 'nullable|date',
      'hasta' => 'nullable|date',
    ]);

    $pedidos = ExpPedido::with(['cliente.representacion', 'molino.molino', 'lineas'])
      ->when(!$request->boolean('historico'), fn($q) => $q->vigentes())
      ->when($request->filled('estado'), fn($q) => $q->where('estado', $request->estado))
      ->when($request->filled('cliente_id'), fn($q) => $q->where('expedicion_cliente_id', $request->cliente_id))
      ->when($request->filled('molino_id'), fn($q) => $q->where('expedicion_molino_id', $request->molino_id))
      ->when($request->filled('numero'), fn($q) => $q->where('numero', (int) $request->numero))
      ->when($request->filled('desde'), fn($q) => $q->whereDate('fecha_pedido', '>=', $request->desde))
      ->when($request->filled('hasta'), fn($q) => $q->whereDate('fecha_pedido', '<=', $request->hasta))
      ->orderByRaw('numero IS NULL DESC')
      ->orderByDesc('numero')
      ->orderByDesc('revision')
      ->orderByDesc('id')
      ->paginate(20)
      ->withQueryString();

    return view('pages.Expedicion.Pedido.index', array_merge(
      ['pedidos' => $pedidos],
      $this->listas()
    ));
  }

  public function create()
  {
    return view('pages.Expedicion.Pedido.form', array_merge(
      ['pedido' => new ExpPedido(['fecha_pedido' => now(), 'tipo' => 'normal'])],
      $this->listas()
    ));
  }

  public function store(Request $request)
  {
    [$datos, $lineas] = $this->validar($request);

    $pedido = $this->central->crear($datos, $lineas, $request->boolean('confirmar'), auth()->id());

    return redirect()->route('expedicion_pedido.show', $pedido)
      ->with('success', $pedido->numero
        ? "Pedido {$pedido->codigo} confirmado."
        : 'Pedido guardado como pendiente de confirmación.');
  }

  public function show(ExpPedido $expedicion_pedido)
  {
    $pedido = $expedicion_pedido->load(['cliente.representacion', 'molino.molino', 'lineas.entrega', 'movimientos.usuario', 'usuario']);
    $revisiones = $pedido->numero ? $pedido->revisiones()->get() : collect([$pedido]);

    return view('pages.Expedicion.Pedido.show', [
      'pedido' => $pedido,
      'revisiones' => $revisiones,
      'destinatarios' => app(Notificador::class)->destinatarios(
        $pedido->revision > 0 ? 'pedido_modificado' : 'pedido_nuevo',
        $pedido->expedicion_molino_id,
        $pedido->expedicion_cliente_id
      ),
    ]);
  }

  public function edit(ExpPedido $expedicion_pedido)
  {
    abort_unless($expedicion_pedido->esEditable(), 403, 'Este pedido ya no se puede modificar.');

    return view('pages.Expedicion.Pedido.form', array_merge(
      ['pedido' => $expedicion_pedido->load('lineas')],
      $this->listas()
    ));
  }

  public function update(Request $request, ExpPedido $expedicion_pedido)
  {
    abort_unless($expedicion_pedido->esEditable(), 403, 'Este pedido ya no se puede modificar.');
    [$datos, $lineas] = $this->validar($request);

    $pedido = $this->central->actualizar($expedicion_pedido, $datos, $lineas, auth()->id());

    $mensaje = $pedido->id !== $expedicion_pedido->id
      ? "Se creó la revisión {$pedido->codigo}. Recordá enviarla al molino."
      : 'Pedido actualizado.';

    return redirect()->route('expedicion_pedido.show', $pedido)->with('success', $mensaje);
  }

  public function destroy(ExpPedido $expedicion_pedido)
  {
    $this->central->cambiarEstado($expedicion_pedido, ExpPedido::ANULADO, auth()->id(), 'Anulado desde la lista.');

    return redirect()->route('expedicion_pedido.index')->with('success', "Pedido {$expedicion_pedido->codigo} anulado.");
  }

  public function confirmar(ExpPedido $pedido)
  {
    $pedido = $this->central->confirmar($pedido, auth()->id());

    return redirect()->route('expedicion_pedido.show', $pedido)->with('success', "Pedido confirmado con el número {$pedido->codigo}.");
  }

  public function estado(Request $request, ExpPedido $pedido)
  {
    $validated = $request->validate([
      'estado' => ['required', Rule::in(array_keys(ExpPedido::ESTADOS))],
      'motivo' => 'nullable|string|max:255',
    ]);

    $pedido = $this->central->cambiarEstado($pedido, $validated['estado'], auth()->id(), $validated['motivo'] ?? null);

    return redirect()->route('expedicion_pedido.show', $pedido)->with('success', "Estado: {$pedido->estado_nombre}.");
  }

  /**
   * Vista previa de la planilla en PDF.
   */
  public function planilla(ExpPedido $pedido)
  {
    return response($this->planilla->pdf($pedido), 200, [
      'Content-Type' => 'application/pdf',
      'Content-Disposition' => 'inline; filename="' . $pedido->nombreArchivo() . '"',
    ]);
  }

  /**
   * Genera y archiva la planilla y la manda a los destinatarios configurados del molino.
   */
  public function enviar(ExpPedido $pedido, Notificador $notificador)
  {
    abort_unless($pedido->numero && $pedido->vigente, 422, 'Solo se envían pedidos confirmados y vigentes.');
    abort_if($pedido->estado === ExpPedido::ANULADO, 422, 'El pedido está anulado.');

    $evento = $pedido->revision > 0 ? 'pedido_modificado' : 'pedido_nuevo';
    $destinatarios = $notificador->destinatarios($evento, $pedido->expedicion_molino_id, $pedido->expedicion_cliente_id);

    try {
      $ruta = $this->planilla->archivar($pedido);
      $enviados = $notificador->enviar(
        $destinatarios,
        new PedidoMolinoMail($pedido, $ruta),
        "Fraschina - {$pedido->codigo} - {$pedido->cliente->sigla}: " . $this->central->resumen($pedido)
      );
    } catch (\Exception $e) {
      report($e);
      return back()->with('error', 'No se pudo generar o enviar la planilla. Intente nuevamente o contacte al administrador.');
    }

    $this->central->registrarEnvio($pedido, $ruta, implode(', ', $enviados), auth()->id());

    $mensaje = $enviados
      ? 'Planilla enviada a: ' . implode(', ', $enviados) . '.'
      : 'Planilla archivada. No hay destinatarios configurados para este molino: cargalos en Avisos.';

    return redirect()->route('expedicion_pedido.show', $pedido)->with($enviados ? 'success' : 'warning', $mensaje);
  }

  /**
   * Condiciones habituales de una combinación cliente-molino (para precargar el formulario).
   */
  public function condiciones(Request $request)
  {
    $request->validate([
      'cliente_id' => 'required|integer',
      'molino_id' => 'required|integer',
    ]);

    $config = ExpedicionMolinoTextoCliente::para((int) $request->cliente_id, (int) $request->molino_id);

    return response()->json([
      'plazo_dias' => $config?->plazo_dias,
      'forma_pago' => $config?->forma_pago,
      'condicion' => $config?->condicion,
      'planta' => $config?->planta,
      'configurado' => (bool) $config,
    ]);
  }

  private function listas(): array
  {
    return [
      'clientes' => ExpedicionCliente::with('representacion:id,razonsocial')->orderBy('sigla')->get(),
      'molinos' => ExpedicionMolino::with('molino:id,razonsocial')->orderBy('sigla')->get(),
      'estados' => ExpPedido::ESTADOS,
      'tipos' => ExpPedido::TIPOS,
    ];
  }

  /**
   * @return array{0: array, 1: array} datos del pedido y líneas
   */
  private function validar(Request $request): array
  {
    $validated = $request->validate([
      'expedicion_cliente_id' => ['required', Rule::exists('expedicion_clientes', 'id')->whereNull('deleted_at')],
      'expedicion_molino_id' => ['required', Rule::exists('expedicion_molinos', 'id')->whereNull('deleted_at')],
      'tipo' => ['required', Rule::in(array_keys(ExpPedido::TIPOS))],
      'fecha_pedido' => 'required|date',
      'observaciones' => 'nullable|string|max:2000',
      'lineas' => 'required|array|min:1',
      'lineas.*.calidad' => 'required|string|max:100',
      'lineas.*.tolvas' => 'nullable|integer|min:0',
      'lineas.*.bolsas' => 'nullable|integer|min:0',
      'lineas.*.kilos' => 'nullable|integer|min:0',
      'lineas.*.precio' => 'nullable|numeric|min:0',
      'lineas.*.condicion' => 'nullable|string|max:100',
      'lineas.*.planta' => 'nullable|string|max:100',
      'lineas.*.fecha_entrega' => 'nullable|date',
      'lineas.*.fecha_tentativa' => 'nullable|boolean',
      'lineas.*.nota_fecha' => 'nullable|string|max:255',
      'lineas.*.fecha_alternativa' => 'nullable|date',
      'lineas.*.hora' => 'nullable|string|max:20',
      'lineas.*.plazo_dias' => 'nullable|integer|min:0|max:365',
      'lineas.*.forma_pago' => 'nullable|string|max:100',
      'lineas.*.lugar_entrega' => 'nullable|string|max:255',
      'lineas.*.observaciones' => 'nullable|string|max:1000',
    ], [
      'lineas.required' => 'El pedido necesita al menos una línea.',
      'lineas.*.calidad.required' => 'Cada línea necesita la calidad.',
    ]);

    foreach ($validated['lineas'] as $i => $linea) {
      if (empty($linea['tolvas']) && empty($linea['bolsas'])) {
        throw \Illuminate\Validation\ValidationException::withMessages([
          "lineas.$i.tolvas" => 'Cada línea debe tener tolvas o bolsas.',
        ]);
      }
    }

    $lineas = $validated['lineas'];
    unset($validated['lineas']);

    return [$validated, $lineas];
  }
}
