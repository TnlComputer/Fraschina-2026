<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Expedición → Historial (planillas): consulta de lo importado de Diaria y Ctrol, tal cual estaba en las planillas.
 */
class ExpedicionHistorialController extends Controller
{
  public function index(Request $request)
  {
    $vista = $request->vista === 'control' ? 'control' : 'diaria';
    $tabla = $vista === 'control' ? 'exp_historial_control' : 'exp_historial_diaria';
    $columnaFecha = $request->por === 'entrega' && $vista === 'diaria' ? 'fecha_entrega' : 'fecha';

    $registros = DB::table($tabla)
      ->when($request->filled('desde'), fn($q) => $q->whereDate($columnaFecha, '>=', $request->desde))
      ->when($request->filled('hasta'), fn($q) => $q->whereDate($columnaFecha, '<=', $request->hasta))
      ->when($request->filled('molino'), fn($q) => $q->where('molino_codigo', $request->molino))
      ->when($request->filled('cliente'), fn($q) => $q->where('cliente_numero', $request->cliente))
      ->when($vista === 'diaria' && $request->filled('accion'), fn($q) => $q->where('accion', $request->accion))
      ->when($vista === 'diaria' && $request->filled('pedido'), fn($q) => $q->where(fn($w) => $w->where('pedido', $request->pedido)->orWhere('ra', $request->pedido)))
      ->when($request->filled('buscar'), fn($q) => $q->where(fn($w) => $vista === 'control'
        ? $w->where('detalle', 'like', "%{$request->buscar}%")->orWhere('transporte', 'like', "%{$request->buscar}%")->orWhere('estado', 'like', "%{$request->buscar}%")
        : $w->where('observaciones', 'like', "%{$request->buscar}%")->orWhere('calidad', 'like', "%{$request->buscar}%")))
      ->orderByDesc('fecha')->orderByDesc('fila')
      ->paginate(50)->withQueryString();

    return view('pages.Expedicion.historial', [
      'vista' => $vista,
      'registros' => $registros,
      'molinos' => DB::table('expedicion_molinos')->whereNotNull('codigo')->orderBy('codigo')->pluck('sigla', 'codigo'),
      'clientes' => DB::table('expedicion_clientes')->whereNotNull('numero')->orderBy('numero')->pluck('nombre_planilla', 'numero'),
      'acciones' => ['PedN' => 'Pedido nuevo', 'ConfF' => 'Confirma fecha', 'CambF' => 'Cambia fecha', 'SuspF' => 'Suspende'],
    ]);
  }
}
