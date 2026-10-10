<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Alta, edición y baja comunes para las tablas auxiliares (calles, barrios, rubros, etc.).
 *
 * Cada controlador define auxiliar() con:
 *  - modelo:  clase del modelo
 *  - titulo:  nombre en singular ("Barrio")
 *  - ruta:    prefijo de las rutas del recurso ("barrios")
 *  - campos:  [columna => [label, tipo (text|time|color|usos|number), requerido, max]]
 *  - femenino: true si el título es femenino ("Nueva calle", "creada")
 *  - usado_en: 'tabla.columna' que lo referencian (ej. ['distribucions.barrio_id']) para no eliminarlo si está en uso
 *
 * La baja es lógica (deleted_at): el registro queda en la base y deja de ofrecerse.
 */
trait CrudAuxiliar
{
  abstract protected function auxiliar(): array;

  /** Columnas "se usa en" (SI/NO) que tienen varias tablas auxiliares. */
  protected static array $usos = [
    'representaciones' => 'Representación', 'distribuciones' => 'Distribución', 'molinos' => 'Molinos',
    'proveedores' => 'Proveedores', 'agros' => 'Agro', 'transportes' => 'Transporte', 'agendas' => 'Agenda general',
  ];

  public function create()
  {
    return $this->formularioAuxiliar(new ($this->auxiliar()['modelo']));
  }

  public function store(Request $request)
  {
    $cfg = $this->auxiliar();
    $registro = new $cfg['modelo'];
    $registro->forceFill($this->datosAuxiliar($request, $registro))->save();

    return redirect()->route($cfg['ruta'] . '.index')->with('success', $cfg['titulo'] . ' ' . $this->generoAuxiliar('creado') . ' correctamente.');
  }

  public function show($id)
  {
    return redirect()->route($this->auxiliar()['ruta'] . '.edit', $id);
  }

  public function edit($id)
  {
    $registro = $this->auxiliar()['modelo']::findOrFail($id);
    if ($this->esSinDato($registro)) {
      return $this->avisoSinDato();
    }
    return $this->formularioAuxiliar($registro);
  }

  public function update(Request $request, $id)
  {
    $cfg = $this->auxiliar();
    $registro = $cfg['modelo']::findOrFail($id);
    if ($this->esSinDato($registro)) {
      return $this->avisoSinDato();
    }
    $registro->forceFill($this->datosAuxiliar($request, $registro))->save();

    return redirect()->route($cfg['ruta'] . '.index')->with('success', $cfg['titulo'] . ' ' . $this->generoAuxiliar('actualizado') . ' correctamente.');
  }

  public function destroy($id)
  {
    $cfg = $this->auxiliar();
    $registro = $cfg['modelo']::findOrFail($id);
    if ($this->esSinDato($registro)) {
      return $this->avisoSinDato();
    }

    $usos = $this->usosAuxiliar($registro->getKey());
    if ($usos) {
      return back()->with('error', "No se puede eliminar «{$this->nombreAuxiliar($registro)}»: está en uso ({$usos}).");
    }

    $registro->delete();

    return redirect()->route($cfg['ruta'] . '.index')->with('success', $cfg['titulo'] . ' ' . $this->generoAuxiliar('eliminado') . '.');
  }

  private function formularioAuxiliar($registro)
  {
    $cfg = $this->auxiliar();

    return view('pages.Tools.auxiliar-form', [
      'cfg' => $cfg,
      'registro' => $registro,
      'usos' => self::$usos,
      'colores' => ['Rojo', 'Naranja', 'Amarillo', 'Verde', 'Celeste', 'Azul', 'Violeta', 'Rosa', 'Gris'],
    ]);
  }

  private function datosAuxiliar(Request $request, $registro): array
  {
    $cfg = $this->auxiliar();
    $tabla = $registro->getTable();
    $reglas = [];
    foreach ($cfg['campos'] as $columna => $campo) {
      if (($campo['tipo'] ?? 'text') === 'usos') {
        continue;
      }
      $r = [($campo['requerido'] ?? false) ? 'required' : 'nullable'];
      array_push($r, ...match ($campo['tipo'] ?? 'text') {
        'time' => ['date_format:H:i'],
        'number' => ['integer'],
        default => ['string', 'max:' . ($campo['max'] ?? 150)],
      });
      if ($campo['unico'] ?? false) {
        $r[] = \Illuminate\Validation\Rule::unique($tabla, $columna)->ignore($registro->getKey())->whereNull('deleted_at');
      }
      $reglas[$columna] = $r;
    }
    $datos = $request->validate($reglas, [], collect($cfg['campos'])->map(fn($c) => mb_strtolower($c['label']))->all());

    // Marcas "se usa en": SI / NO
    foreach ($cfg['campos'] as $columna => $campo) {
      if (($campo['tipo'] ?? 'text') === 'usos') {
        foreach (array_keys(self::$usos) as $uso) {
          if (Schema::hasColumn($tabla, $uso)) {
            $datos[$uso] = $request->boolean("usos.$uso") ? 'SI' : 'NO';
          }
        }
      }
    }

    return $datos;
  }

  /**
   * Cuántos registros de otras tablas usan este valor (por las columnas de usado_en). Texto vacío = sin uso.
   */
  private function usosAuxiliar($id): string
  {
    $detalle = [];
    foreach ($this->auxiliar()['usado_en'] ?? [] as $referencia) {
      [$tabla, $columna] = explode('.', $referencia);
      if (!Schema::hasColumn($tabla, $columna)) {
        continue;
      }
      $q = DB::table($tabla)->where($columna, $id);
      if (Schema::hasColumn($tabla, 'deleted_at')) {
        $q->whereNull('deleted_at');
      }
      if ($n = $q->count()) {
        $detalle[] = "$n en $tabla";
      }
    }
    return implode(', ', $detalle);
  }

  private function nombreAuxiliar($registro): string
  {
    return (string) $registro->getAttribute(array_key_first($this->auxiliar()['campos']));
  }

  /** "creado" → "creada" si el título es femenino. */
  private function generoAuxiliar(string $palabra): string
  {
    return ($this->auxiliar()['femenino'] ?? false) ? substr($palabra, 0, -1) . 'a' : $palabra;
  }

  /**
   * La fila con nombre vacío es la opción "sin dato": sirve para cargar un registro sin ese dato
   * o para quitárselo, por eso no se renombra ni se elimina.
   */
  private function esSinDato($registro): bool
  {
    return trim($this->nombreAuxiliar($registro)) === '';
  }

  private function avisoSinDato()
  {
    return redirect()->route($this->auxiliar()['ruta'] . '.index')
      ->with('info', '«(sin dato)» se usa para cargar registros sin este dato o para quitárselo: no se modifica ni se elimina.');
  }
}
