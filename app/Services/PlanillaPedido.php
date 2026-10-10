<?php

namespace App\Services;

use App\Models\ExpPedido;
use Dompdf\Dompdf;
use Illuminate\Support\Facades\Storage;

/**
 * Genera la planilla de pedido que se envía al molino (antes "fax de pedido").
 * El contenido se arma según la configuración de la combinación cliente-molino.
 */
class PlanillaPedido
{
  public function datos(ExpPedido $pedido): array
  {
    $pedido->loadMissing(['lineas', 'cliente.representacion', 'molino.molino']);
    $config = $pedido->configuracion();

    $textos = $config?->lineasTexto() ?: $pedido->cliente->lineasTexto();

    return [
      'pedido' => $pedido,
      'titulo' => config('expedicion.nombre_planilla'),
      // null en la configuración = se usa el criterio del molino
      'mostrarPrecio' => $config?->mostrar_precio ?? $pedido->molino->muestraPrecio(),
      'subrayarPrecio' => (bool) $config?->subrayar_precio,
      'mostrarFechaAlternativa' => $config ? $config->mostrar_fecha_alternativa : true,
      'mostrarEspeciales' => $config ? $config->mostrar_especiales : true,
      'textos' => $textos,
      'anterior' => $pedido->revision > 0
        ? ExpPedido::with('lineas')->where('numero', $pedido->numero)->where('revision', $pedido->revision - 1)->first()
        : null,
    ];
  }

  public function pdf(ExpPedido $pedido): string
  {
    $dompdf = new Dompdf();
    $dompdf->loadHtml(view('pages.Expedicion.Pedido.planilla', $this->datos($pedido))->render());
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();

    return $dompdf->output();
  }

  /**
   * Genera el PDF y lo guarda con el nombre en código. Devuelve la ruta relativa en el disco.
   */
  public function archivar(ExpPedido $pedido): string
  {
    $ruta = trim(config('expedicion.carpeta_planillas'), '/') . '/' . $pedido->nombreArchivo();
    Storage::disk(config('expedicion.disco'))->put($ruta, $this->pdf($pedido));

    return $ruta;
  }
}
