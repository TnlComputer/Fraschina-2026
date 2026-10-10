<?php

namespace App\Http\Controllers;

use App\Models\DistribucionNroPedidos;
use Dompdf\Dompdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use NumberToWords\NumberToWords;

class DistribucionRepartoController extends Controller
{
  /**
   * Relaciones de la distribución que usan las vistas de reparto (evita N+1).
   */
  private const DIRECCION = [
    'distribucion.auxCalles',
    'distribucion.auxbarrio',
    'distribucion.auxlocalidad',
    'distribucion.auxmunicipio',
  ];

  public function index(Request $request)
  {
    $fecha = $this->fechaValida($request);

    $distribuciones = DistribucionNroPedidos::with(self::DIRECCION)
      ->where('status', 'A')
      ->whereDate('fechaEntrega', $fecha)
      ->conLineas()
      ->ordenReparto()
      ->get();

    return view('pages.Distribucion.Reparto.index', compact('distribuciones', 'fecha'));
  }

  /**
   * Días con pedidos de reparto entre dos fechas (para resaltarlos en el calendario).
   * Devuelve {"2026-10-06": 12, ...} con la cantidad de pedidos por día.
   */
  public function fechasConPedidos(Request $request)
  {
    $request->validate([
      'desde' => 'required|date_format:Y-m-d',
      'hasta' => 'required|date_format:Y-m-d|after_or_equal:desde',
    ]);

    $dias = DistribucionNroPedidos::where('status', 'A')
      ->conLineas()
      ->whereDate('fechaEntrega', '>=', $request->desde)
      ->whereDate('fechaEntrega', '<=', $request->hasta)
      ->selectRaw('DATE(fechaEntrega) as dia, COUNT(*) as pedidos')
      ->groupBy('dia')
      ->pluck('pedidos', 'dia');

    return response()->json($dias);
  }

  public function show($id)
  {
    $pedido = DistribucionNroPedidos::with([
      'distribucion',
      'lineasPedidos' => fn($query) => $query->orderBy('linea'),
      'lineasPedidos.producto',
      'lineasTareas' => fn($query) => $query->orderBy('linea'),
      'lineasTareas.tarea',
    ])->find($id);

    if (!$pedido) {
      return redirect()->route('distribucion_reparto.index')->with('error', 'Pedido no encontrado.');
    }

    return view('pages.Distribucion.Reparto.pedido', compact('pedido'));
  }

  public function update(Request $request, $id)
  {
    $validated = $request->validate([
      'fechaFactura' => 'nullable|date',
      'nroFactura' => 'nullable|string|max:20',
      'totalFactura' => 'nullable|numeric',
      'chofer' => 'nullable|string|max:2',
      'orden' => 'nullable|integer',
      'fecha' => 'nullable|date_format:Y-m-d',
    ]);

    $pedido = DistribucionNroPedidos::findOrFail($id);
    $pedido->fechaFactura = $validated['fechaFactura'] ?? null;
    $pedido->nroFactura = $validated['nroFactura'] ?? null;
    $pedido->totalFactura = $validated['totalFactura'] ?? null;
    $pedido->chofer = $validated['chofer'] ?? null;
    $pedido->orden = $validated['orden'] ?? null;
    $pedido->save();

    return redirect()->route('distribucion_reparto.index', ['fecha' => $validated['fecha'] ?? null])
      ->with('success', 'Distribución actualizada correctamente');
  }

  public function convertirNumeroALetras($numero, $moneda = 'Pesos')
  {
    $numero = (float) $numero;

    // Separamos la parte entera y fraccionaria (centavos)
    $entero = floor($numero);
    $centavos = round(($numero - $entero) * 100);

    // Convertimos la parte entera a letras
    $numberToWords = new NumberToWords();
    $numberTransformer = $numberToWords->getNumberTransformer('es');
    $numeroEnLetras = $numberTransformer->toWords($entero);

    // Si hay centavos, los agregamos a la cadena
    $centavosEnLetras = '';
    if ($centavos > 0) {
      $centavosEnLetras = ' con ' . $numberTransformer->toWords($centavos) . ' centavos';
    }

    return 'La suma de ' . $moneda . ': ' . ucfirst($numeroEnLetras) . $centavosEnLetras;
  }

  public function imprimirRecibo($id)
  {
    // LEFT JOIN: un cliente sin calle cargada igual debe poder imprimir su recibo
    $pedido = DB::table('distribucion_nropedidos as dn')
      ->join('distribucions as d', 'dn.distribucion_id', '=', 'd.id')
      ->leftJoin('auxcalles as ac', 'd.dire_calle_id', '=', 'ac.id')
      ->select(
        'dn.*',
        'd.nomfantasia',
        'd.razonsocial',
        'ac.calle as direccion',
        'd.telefono',
        'dn.totalFactura'
      )
      ->where('dn.id', $id)
      ->first();

    if (!$pedido) {
      return redirect()->route('distribucion_reparto.index')->with('error', 'Pedido no encontrado.');
    }

    $importeTexto = $this->convertirNumeroALetras($pedido->totalFactura);

    return $this->pdf(
      view('pages.Distribucion.Printer.recibo', ['pedido' => $pedido, 'importeTexto' => $importeTexto]),
      'recibo.pdf',
      'portrait'
    );
  }

  public function imprimirReparto(Request $request)
  {
    $fecha = $this->fechaValida($request);

    $distribuciones = DistribucionNroPedidos::with(array_merge(self::DIRECCION, [
      'lineasPedidos' => fn($query) => $query->orderBy('linea'),
      'lineasPedidos.producto',
      'lineasTareas' => fn($query) => $query->orderBy('linea'),
      'lineasTareas.tarea',
      'distribucion.auxzona',
      'distribucion.auxcobro',
      'distribucion.auxpago',
      'distribucion.auxtcobro',
    ]))
      ->where('status', 'A')
      ->whereDate('fechaEntrega', '=', $fecha)
      ->conLineas()
      ->ordenReparto()
      ->get();

    return $this->pdf(
      view('pages.Distribucion.Printer.reparto', ['distribuciones' => $distribuciones, 'fecha' => $fecha]),
      'Reparto-' . $fecha . '.pdf',
      'landscape'
    );
  }

  public function imprimirControl(Request $request)
  {
    $fecha = $this->fechaValida($request);

    $distribuciones = DistribucionNroPedidos::with(['lineasPedidos', 'distribucion.auxpago'])
      ->where('status', 'A')
      ->whereIn('tipo', ['P', 'PT'])
      ->whereDate('fechaEntrega', '=', $fecha)
      ->conLineas()
      ->ordenReparto()
      ->get();

    return $this->pdf(
      view('pages.Distribucion.Printer.control', ['distribuciones' => $distribuciones, 'fecha' => $fecha]),
      'Control-' . $fecha . '.pdf',
      'portrait',
      numerarPaginas: true
    );
  }

  /**
   * Fecha del request (Y-m-d) o la de hoy. Una fecha mal formada vuelve con error de validación.
   */
  private function fechaValida(Request $request): string
  {
    $request->validate(['fecha' => 'nullable|date_format:Y-m-d']);

    return $request->get('fecha') ?: now()->toDateString();
  }

  /**
   * Renderiza una vista a PDF con Dompdf y la muestra en el navegador.
   */
  private function pdf($view, string $nombre, string $orientacion, bool $numerarPaginas = false)
  {
    try {
      $dompdf = new Dompdf();
      $dompdf->loadHtml($view->render());
      $dompdf->setPaper('A4', $orientacion);
      $dompdf->render();

      if ($numerarPaginas) {
        $dompdf->getCanvas()->page_script(function ($pageNumber, $pageCount, $canvas) {
          $canvas->text(500, 820, "Página $pageNumber de $pageCount", null, 10, [0, 0, 0]);
        });
      }

      return response($dompdf->output(), 200, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'inline; filename="' . $nombre . '"',
      ]);
    } catch (\Exception $e) {
      report($e);
      return redirect()->back()->with('error', 'No se pudo generar el PDF. Intente nuevamente o contacte al administrador.');
    }
  }
}
