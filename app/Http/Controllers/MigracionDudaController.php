<?php

namespace App\Http\Controllers;

use App\Models\MigracionDuda;
use Illuminate\Http\Request;

/**
 * Expedición → Dudas de migración: lo que hay que preguntarle al cliente sobre las planillas, con lugar para la respuesta.
 */
class MigracionDudaController extends Controller
{
  public function index(Request $request)
  {
    $estado = in_array($request->estado, ['pendientes', 'resueltas', 'todas'], true) ? $request->estado : 'pendientes';
    $dudas = MigracionDuda::with('resolvio')
      ->when($estado === 'pendientes', fn($q) => $q->whereNull('resuelta_at'))
      ->when($estado === 'resueltas', fn($q) => $q->whereNotNull('resuelta_at'))
      ->orderBy('id')->get();

    return view('pages.Expedicion.dudas', [
      'dudas' => $dudas,
      'estado' => $estado,
      'pendientes' => MigracionDuda::whereNull('resuelta_at')->count(),
      'resueltas' => MigracionDuda::whereNotNull('resuelta_at')->count(),
    ]);
  }

  public function update(Request $request, MigracionDuda $duda)
  {
    $datos = $request->validate(['respuesta' => 'nullable|string|max:5000', 'resuelta' => 'nullable|boolean']);
    $duda->respuesta = $datos['respuesta'] ?? null;
    $resuelta = $request->boolean('resuelta');
    $duda->resuelta_at = $resuelta ? ($duda->resuelta_at ?? now()) : null;
    $duda->resuelta_por = $resuelta ? ($duda->resuelta_por ?? $request->user()->id) : null;
    $duda->save();   // queda en la auditoría

    return redirect()->to(url()->previous() . '#duda-' . $duda->id)->with('success', $resuelta ? 'Duda marcada como resuelta.' : 'Respuesta guardada.');
  }
}
