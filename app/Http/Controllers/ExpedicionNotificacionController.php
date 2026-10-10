<?php

namespace App\Http\Controllers;

use App\Models\ExpedicionCliente;
use App\Models\ExpedicionMolino;
use App\Models\ExpNotificacionDestinatario;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Destinatarios de los avisos de la Central de Pedidos (mail y WhatsApp), activables por evento.
 */
class ExpedicionNotificacionController extends Controller
{
  public function index()
  {
    return view('pages.Expedicion.notificaciones', [
      'destinatarios' => ExpNotificacionDestinatario::with(['molino', 'cliente'])
        ->orderBy('expedicion_molino_id')->orderBy('evento')->get(),
      'molinos' => ExpedicionMolino::orderBy('sigla')->get(),
      'clientes' => ExpedicionCliente::orderBy('sigla')->get(),
      'canales' => ExpNotificacionDestinatario::CANALES,
      'eventos' => ExpNotificacionDestinatario::EVENTOS,
    ]);
  }

  public function store(Request $request)
  {
    $validated = $request->validate([
      'expedicion_molino_id' => 'nullable|exists:expedicion_molinos,id',
      'expedicion_cliente_id' => 'nullable|exists:expedicion_clientes,id',
      'canal' => ['required', Rule::in(array_keys(ExpNotificacionDestinatario::CANALES))],
      'destino' => ['required', 'string', 'max:150', $request->canal === 'mail' ? 'email' : 'regex:/^\+?[0-9 ]{8,20}$/'],
      'eventos' => 'required|array|min:1',
      'eventos.*' => Rule::in(array_keys(ExpNotificacionDestinatario::EVENTOS)),
    ], [
      'destino.regex' => 'El número de WhatsApp debe tener solo dígitos (con código de país, ej. +54911...).',
    ]);

    foreach ($validated['eventos'] as $evento) {
      ExpNotificacionDestinatario::firstOrCreate([
        'expedicion_molino_id' => $validated['expedicion_molino_id'] ?? null,
        'expedicion_cliente_id' => $validated['expedicion_cliente_id'] ?? null,
        'canal' => $validated['canal'],
        'destino' => $validated['destino'],
        'evento' => $evento,
      ], ['activo' => true]);
    }

    return back()->with('success', 'Destinatario agregado.');
  }

  public function update(ExpNotificacionDestinatario $destinatario)
  {
    $destinatario->update(['activo' => !$destinatario->activo]);

    return back()->with('success', $destinatario->activo ? 'Aviso activado.' : 'Aviso desactivado.');
  }

  public function destroy(ExpNotificacionDestinatario $destinatario)
  {
    $destinatario->delete();

    return back()->with('success', 'Destinatario eliminado.');
  }
}
