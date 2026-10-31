<?php

namespace App\Http\Controllers;

use App\Exports\ExpedicionGeneralExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\ExpedicionGeneral;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Carbon\Carbon;


class ExpedicionGeneralController extends Controller
{
  /**
   * Campos que se filtran con LIKE desde el formulario de búsqueda.
   */
  private const CAMPOS_FILTRO = [
    'mo', 'cl', 'modo', 'prod', 'p', 'l', 'pl', 'w', 'gh', 'gs', 'gi',
    'hum', 'cz', 'est', 'abs', 'fn', 'punt', 'particularidades',
  ];

  public function index(Request $request)
  {
    $expedicion_general = $this->consultaFiltrada($request)
      ->orderBy('fecha', 'desc')
      ->paginate(10)
      ->withQueryString();

    return view('pages.Expedicion.General.index', compact('expedicion_general'));
  }

  // Mostrar el formulario para crear un nuevo registro
  public function create()
  {
    return view('pages.Expedicion.General.create');
  }

  // Almacenar el nuevo registro en la base de datos
  public function store(Request $request)
  {
    $validated = $request->validate($this->reglas());

    $registro = ExpedicionGeneral::create($validated);

    return $this->volver($registro, 'Expedición creada exitosamente.');
  }

  public function show(ExpedicionGeneral $expedicionGeneral)
  {
    //
  }

  // Mostrar el formulario para editar un registro
  public function edit($id)
  {
    $expedicionGeneral = $this->activos()->findOrFail($id);
    return view('pages.Expedicion.General.edit', compact('expedicionGeneral'));
  }

  // Actualizar un registro existente
  public function update(Request $request, $id)
  {
    $validated = $request->validate($this->reglas());

    $expedicionGeneral = $this->activos()->findOrFail($id);
    $expedicionGeneral->update($validated);

    return $this->volver($expedicionGeneral, 'Expedición actualizada exitosamente.');
  }

  /**
   * Vuelve al listado. Si quedaron valores fuera de los mínimos y máximos se guarda igual y se avisa cuáles.
   */
  private function volver(ExpedicionGeneral $registro, string $mensaje)
  {
    $redireccion = redirect()->route('expedicion_general.index')->with('success', $mensaje);
    $fuera = $registro->camposFueraDeRango();
    return $fuera ? $redireccion->with('warning', 'Quedaron fuera de rango: ' . implode(', ', $fuera) . '.') : $redireccion;
  }

  public function destroy($id)
  {
    $expedicionGeneral = ExpedicionGeneral::findOrFail($id);

    // Baja lógica: "A" -> "D"
    $expedicionGeneral->status = 'D';
    $expedicionGeneral->save();

    return redirect()->route('expedicion_general.index')->with('success', 'Expedición desactivada correctamente.');
  }

  public function export(Request $request)
  {
    $expediciones = $this->consultaFiltrada($request)->orderBy('fecha', 'desc')->get();

    // Si no hay datos, mostrar un mensaje en lugar de generar un archivo vacío
    if ($expediciones->isEmpty()) {
      return back()->with('error', 'No hay datos para exportar con los filtros aplicados.');
    }

    $nombreArchivo = 'expedicion_general-' . Carbon::now()->format('Y-m-d') . '.xlsx';

    return Excel::download(new ExpedicionGeneralExport($expediciones), $nombreArchivo);
  }

  /**
   * Registros no dados de baja.
   */
  private function activos(): Builder
  {
    return ExpedicionGeneral::query()->where(function ($q) {
      $q->where('status', '!=', 'D')->orWhereNull('status');
    });
  }

  /**
   * Consulta con los filtros del formulario. Compartida por index() y export().
   * La fecha acepta AAAA (año), AAAAMM (año y mes) o AAAAMMDD (día).
   */
  private function consultaFiltrada(Request $request): Builder
  {
    $query = $this->activos();

    $fecha = trim((string) $request->input('fecha'));
    if (preg_match('/^\d{4}$/', $fecha)) {
      $query->whereYear('fecha', $fecha);
    } elseif (preg_match('/^(\d{4})(\d{2})$/', $fecha, $m)) {
      $query->whereYear('fecha', $m[1])->whereMonth('fecha', $m[2]);
    } elseif (preg_match('/^\d{8}$/', $fecha)) {
      try {
        $query->whereDate('fecha', Carbon::createFromFormat('Ymd', $fecha)->format('Y-m-d'));
      } catch (\Exception $e) {
        // Fecha mal formada: no se filtra por fecha
      }
    }

    foreach (self::CAMPOS_FILTRO as $campo) {
      if ($request->filled($campo)) {
        $query->where($campo, 'like', '%' . $request->input($campo) . '%');
      }
    }

    return $query;
  }

  /**
   * Reglas de validación compartidas por store() y update().
   */
  private function reglas(): array
  {
    return [
      'fecha' => 'required|date',
      'mo' => 'required|max:5',
      'cl' => 'required|max:5',
      'modo' => 'nullable|string',
      'prod' => 'nullable|string',
      'p' => 'nullable|numeric',
      'l' => 'nullable|numeric',
      'pl' => 'nullable|numeric',
      'w' => 'nullable|numeric',
      'gh' => 'nullable|numeric',
      'gs' => 'nullable|numeric',
      'gi' => 'nullable|numeric',
      'hum' => 'nullable|numeric',
      'cz' => 'nullable|numeric',
      'est' => 'nullable|numeric',
      'abs' => 'nullable|numeric',
      'fn' => 'nullable|numeric',
      'punt' => 'nullable|numeric',
      'particularidades' => 'nullable|string',
    ];
  }
}
