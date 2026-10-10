<?php

namespace App\Http\Controllers;

use App\Exports\HojaExport;
use App\Models\Auditoria;
use App\Models\User;
use App\Services\Auditoria as ServicioAuditoria;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Log de operaciones por usuario (solo administrador).
 */
class AuditoriaController extends Controller
{
  public function index(Request $request)
  {
    $registros = $this->consulta($request)->paginate(50)->withQueryString();

    return view('pages.Tools.Auditoria.index', [
      'registros' => $registros,
      'usuarios' => User::orderBy('name')->get(['id', 'name']),
      'sectores' => ServicioAuditoria::sectores(),
      'acciones' => Auditoria::ACCIONES,
    ]);
  }

  public function export(Request $request)
  {
    $filas = $this->consulta($request)->limit(50000)->get()->map(fn(Auditoria $a) => [
      $a->created_at?->format('d/m/Y H:i:s'),
      $a->usuario,
      $a->sector,
      $a->accion_nombre,
      $a->tabla,
      $a->registro_id,
      $a->descripcion,
      collect($a->cambios ?? [])->map(fn($v, $k) => "$k: " . ($v[0] ?? '∅') . ' → ' . ($v[1] ?? '∅'))->implode(' | '),
      $a->ip,
    ]);

    return Excel::download(
      new HojaExport('Auditoría', ['Fecha', 'Usuario', 'Sector', 'Acción', 'Tabla', 'Registro', 'Descripción', 'Cambios', 'IP'], $filas),
      'auditoria-' . now()->format('Y-m-d') . '.xlsx'
    );
  }

  private function consulta(Request $request): Builder
  {
    $request->validate([
      'desde' => 'nullable|date',
      'hasta' => 'nullable|date',
      'user_id' => 'nullable|integer',
    ]);

    return Auditoria::query()
      ->when($request->filled('user_id'), fn($q) => $q->where('user_id', $request->user_id))
      ->when($request->filled('sector'), fn($q) => $q->where('sector', $request->sector))
      ->when($request->filled('accion'), fn($q) => $q->where('accion', $request->accion))
      ->when($request->filled('desde'), fn($q) => $q->where('created_at', '>=', $request->date('desde')->startOfDay()))
      ->when($request->filled('hasta'), fn($q) => $q->where('created_at', '<=', $request->date('hasta')->endOfDay()))
      ->when($request->filled('buscar'), function ($q) use ($request) {
        $texto = '%' . $request->buscar . '%';
        $q->where(fn($q) => $q->where('descripcion', 'like', $texto)
          ->orWhere('tabla', 'like', $texto)
          ->orWhere('registro_id', $request->buscar)
          ->orWhere('cambios', 'like', $texto));
      })
      ->latest('id');
  }
}
