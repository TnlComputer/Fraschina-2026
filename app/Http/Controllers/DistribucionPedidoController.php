<?php

namespace App\Http\Controllers;

use App\Models\Distribucion;
use App\Models\DistribucionLineaPedidos;
use App\Models\DistribucionLineaTareas;
use App\Models\DistribucionNroPedidos;
use App\Models\DistribucionProducto;
use App\Models\DistribucionTareas;
use App\Models\productoCDA;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class DistribucionPedidoController extends Controller
{
  public function create(Request $request)
  {
    $clientes = Distribucion::where('status', 'A')
      ->orderBy('nomfantasia')
      ->get(['id', 'nomfantasia', 'razonsocial']);
    return view('pages.Distribucion.Pedido.create', [
      'clientes' => $clientes,
      'clienteId' => (int) old('cliente_id', $request->query('cliente')), // desde la ficha del cliente
      'catalogo' => $this->catalogo(),
    ]);
  }

  public function store(Request $request)
  {
    $request->validate([
      'cliente_id' => 'required|exists:distribucions,id',
      'fecha_entrega' => 'required|date',
      'productos' => 'required|array',
      'productos.*' => 'required|integer',
      'tipos' => 'required|array',
      'tipos.*' => 'required|in:P,T,PT',
      'cantidades' => 'required|array',
      'cantidades.*' => 'nullable|numeric|min:0',
      'precios' => 'required|array',
      'precios.*' => 'nullable|numeric|min:0',
      'detalles' => 'nullable|array',
      'detalles.*' => 'nullable|string|max:1000',
      'flag' => 'nullable|array',
      'sin_cargo' => 'nullable|array',
      'retirar' => 'nullable|array',
      'observaciones' => 'nullable|string',
    ]);

    $clienteId = $request->cliente_id;
    $fechaPedido = now()->toDateString();
    $fechaEntrega = $request->fecha_entrega;
    $tipos = $request->tipos;
    $productos = $request->productos;
    $cantidades = $request->cantidades ?? [];
    $precios = $request->precios ?? [];

    // Productos del cliente en una sola consulta, indexados por producto_id
    $productosCliente = $this->productosDelCliente($clienteId);

    // Validar todos los productos antes de escribir nada: tienen que estar activos en la lista del cliente
    foreach ($productos as $index => $productoId) {
      if (in_array($tipos[$index] ?? null, ['P', 'PT']) && $productosCliente->get($productoId)?->status !== 'A') {
        return back()->withInput()->withErrors(['error' => 'El producto ' . (productoCDA::find($productoId)?->nombre() ?? $productoId) . ' no está en la lista del cliente.']);
      }
    }

    $tipoPedido = (in_array('P', $tipos) && in_array('T', $tipos)) ? 'PT' : (in_array('P', $tipos) ? 'P' : 'T');

    DB::transaction(function () use ($request, $clienteId, $fechaPedido, $fechaEntrega, $tipos, $productos, $cantidades, $precios, $productosCliente, $tipoPedido) {
      $pedido = new DistribucionNroPedidos();
      $pedido->fecha = $fechaPedido;
      $pedido->fechaEntrega = $fechaEntrega;
      $pedido->distribucion_id = $clienteId;
      $pedido->observaciones = $request->observaciones;
      $pedido->status = 'A';
      $pedido->tipo = $tipoPedido;
      $pedido->totalPedido = 0; // Se actualiza al final
      $pedido->save();

      $totalPedido = 0;
      $lineaNumeroP = 1;
      $lineaNumeroT = 1;

      foreach ($productos as $index => $productoId) {
        $tipo = $tipos[$index];

        if ($tipo === 'P' || $tipo === 'PT') {
          $dp = $productosCliente->get($productoId);
          $cantidad = $cantidades[$index] ?? 1;
          $precioUnitario = $precios[$index] ?? $dp->precio ?? 0;
          $totalLinea = productoCDA::calcularTotalLinea($precioUnitario, $cantidad, $this->ivaDe($dp));
          $totalPedido += $totalLinea;

          DistribucionLineaPedidos::create([
            'pedido_id' => $pedido->id,
            'distribucion_id' => $clienteId,
            'fecha' => $fechaPedido,
            'fechaEntrega' => $fechaEntrega,
            'linea' => $lineaNumeroP++,
            'producto_id' => $productoId,
            'cantidad' => $cantidad,
            'precio_unitario' => $precioUnitario,
            'totalLinea' => $totalLinea,
            'bandera' => ($request->flag[$index] ?? null) ? '*' : null,
            'cambiar' => ($request->sin_cargo[$index] ?? null) ? '*' : null,
            'retirar' => ($request->retirar[$index] ?? null) ? '*' : null,
            'status' => 1,
          ]);
        }

        if ($tipo === 'T' || $tipo === 'PT') {
          DistribucionLineaTareas::create([
            'pedido_id' => $pedido->id,
            'distribucion_id' => $clienteId,
            'fecha' => $fechaPedido,
            'fechaEntrega' => $fechaEntrega,
            'linea' => $lineaNumeroT++,
            'tarea_id' => $productoId,
            'detalles' => $request->detalles[$index] ?? null,
            'status' => 'A',
          ]);
        }
      }

      $pedido->update(['totalPedido' => round($totalPedido, 2)]);
      $this->actualizarUltimaEntrega($pedido);
    });

    return redirect()->route('distribucion_agenda.index')->with('success', 'Pedido creado correctamente');
  }

  public function getProductosYTareasPorCliente($clienteId)
  {
    return $this->getProductosTareas($clienteId);
  }

  public function edit($id)
  {
    $pedido = DistribucionNroPedidos::with(['distribucion', 'lineasPedidos.producto', 'lineasTareas.tarea'])->findOrFail($id);

    return view('pages.Distribucion.Pedido.edit', ['pedido' => $pedido, 'catalogo' => $this->catalogo()]);
  }

  public function update(Request $request, $id)
  {
    $request->validate([
      'fecha_entrega' => 'required|date',
      'observaciones' => 'nullable|string',
      'volver' => 'nullable|date_format:Y-m-d', // día de Reparto desde el que se abrió el pedido
      'existing_product_line_ids' => 'nullable|array',
      'existing_product_line_ids.*' => 'integer',
      'existing_precios' => 'nullable|array',
      'existing_precios.*' => 'required|numeric|min:0',
      'existing_cantidades' => 'nullable|array',
      'existing_cantidades.*' => 'required|numeric|min:0',
      'existing_flag' => 'nullable|array',
      'new_flag' => 'nullable|array',
      'existing_sin_cargo' => 'nullable|array',
      'existing_retirar' => 'nullable|array',
      'new_sin_cargo' => 'nullable|array',
      'new_retirar' => 'nullable|array',
      'new_productos' => 'nullable|array',
      'new_productos.*' => 'integer',
      'new_precios' => 'nullable|array',
      'new_precios.*' => 'required|numeric|min:0',
      'new_cantidades' => 'nullable|array',
      'new_cantidades.*' => 'required|numeric|min:0',
      'existing_tarea_detalles' => 'nullable|array',
      'existing_tarea_detalles.*' => 'nullable|string|max:1000',
      'new_tarea_detalles' => 'nullable|array',
      'new_tarea_detalles.*' => 'nullable|string|max:1000',
      'existing_tarea_ids' => 'nullable|array',
      'existing_tarea_ids.*' => 'integer',
      'new_tareas' => 'nullable|array',
      'new_tareas.*' => 'integer|exists:distribucion_tareas,id',
    ]);

    // Los productos nuevos tienen que estar activos en la lista del cliente
    $delCliente = $this->productosDelCliente(DistribucionNroPedidos::findOrFail($id)->distribucion_id);
    foreach ($request->input('new_productos', []) as $productoId) {
      if ($delCliente->get($productoId)?->status !== 'A') {
        return back()->withInput()->withErrors(['error' => 'El producto ' . (productoCDA::find($productoId)?->nombre() ?? $productoId) . ' no está en la lista del cliente.']);
      }
    }

    try {
      $pedido = DB::transaction(function () use ($request, $id) {
        // 1. Datos generales del pedido
        $pedido = DistribucionNroPedidos::lockForUpdate()->findOrFail($id);
        $pedido->fechaEntrega  = $request->input('fecha_entrega');
        $pedido->observaciones = $request->input('observaciones');
        $pedido->save();

        $productosCliente = $this->productosDelCliente($pedido->distribucion_id);

        // Solo se tocan líneas que pertenecen a este pedido
        $lineas = $pedido->lineasPedidos()->get()->keyBy('id');

        // 2. Líneas de productos existentes
        $existingProductLineIds = array_map('intval', $request->input('existing_product_line_ids', []));
        $existingPrecios    = $request->input('existing_precios', []);
        $existingCantidades = $request->input('existing_cantidades', []);
        $marcas = ['bandera' => $request->input('existing_flag', []), 'cambiar' => $request->input('existing_sin_cargo', []), 'retirar' => $request->input('existing_retirar', [])];
        foreach ($existingProductLineIds as $index => $lineaId) {
          $linea = $lineas->get($lineaId);
          if (!$linea) {
            continue;
          }
          $linea->precio_unitario = $existingPrecios[$index] ?? $linea->precio_unitario;
          $linea->cantidad        = $existingCantidades[$index] ?? $linea->cantidad;
          $linea->totalLinea      = productoCDA::calcularTotalLinea(
            $linea->precio_unitario,
            $linea->cantidad,
            $linea->producto?->porcentajeIva() ?? 21.0
          );
          // Flag / sin cargo / retirar: solo se escribe si cambió (lo migrado puede estar como '' o 'SI')
          foreach ($marcas as $campo => $valores) {
            if (isset($valores[$index]) && (bool) $valores[$index] !== DistribucionLineaPedidos::marcado($linea->$campo)) {
              $linea->$campo = $valores[$index] ? '*' : null;
            }
          }
          $linea->save();
        }

        // Eliminar las líneas de productos que se quitaron en la vista
        $idsAEliminar = $lineas->keys()->diff($existingProductLineIds);
        if ($idsAEliminar->isNotEmpty()) {
          DistribucionLineaPedidos::destroy($idsAEliminar->all());
          $lineas = $lineas->except($idsAEliminar->all());
        }

        // 3. Nuevos productos
        $newProductos  = $request->input('new_productos', []);
        $newPrecios    = $request->input('new_precios', []);
        $newCantidades = $request->input('new_cantidades', []);
        $proximaLinea  = (int) $lineas->max('linea');
        foreach ($newProductos as $index => $productoId) {
          $iva = $this->ivaDe($productosCliente->get($productoId));
          $flag = (bool) ($request->input('new_flag', [])[$index] ?? false);
          $sinCargo = (bool) ($request->input('new_sin_cargo', [])[$index] ?? false);
          $retirar = (bool) ($request->input('new_retirar', [])[$index] ?? false);
          // Se suma a la línea del mismo producto solo si tiene las mismas marcas (una bolsa sin cargo va aparte)
          $lineaExistente = $lineas->first(fn($l) => (int) $l->producto_id === (int) $productoId
            && DistribucionLineaPedidos::marcado($l->bandera) === $flag
            && DistribucionLineaPedidos::marcado($l->cambiar) === $sinCargo
            && DistribucionLineaPedidos::marcado($l->retirar) === $retirar);

          if ($lineaExistente) {
            // Sumar la nueva cantidad a la existente y actualizar precio
            $lineaExistente->cantidad += $newCantidades[$index];
            $lineaExistente->precio_unitario = $newPrecios[$index];
            $lineaExistente->totalLinea = productoCDA::calcularTotalLinea($lineaExistente->precio_unitario, $lineaExistente->cantidad, $iva);
            $lineaExistente->save();
          } else {
            $nueva = DistribucionLineaPedidos::create([
              'pedido_id'       => $pedido->id,
              'distribucion_id' => $pedido->distribucion_id,
              'fecha'           => $pedido->fecha,
              'fechaEntrega'    => $pedido->fechaEntrega,
              'linea'           => ++$proximaLinea,
              'producto_id'     => $productoId,
              'cantidad'        => $newCantidades[$index],
              'precio_unitario' => $newPrecios[$index],
              'totalLinea'      => productoCDA::calcularTotalLinea($newPrecios[$index], $newCantidades[$index], $iva),
              'bandera'         => $flag ? '*' : null,
              'cambiar'         => $sinCargo ? '*' : null,
              'retirar'         => $retirar ? '*' : null,
              'status'          => 1,
            ]);
            $lineas->put($nueva->id, $nueva);
          }
        }

        // 4. Tareas existentes: conservar solo las que siguen en la vista
        $existingTareaIds = array_map('intval', $request->input('existing_tarea_ids', []));
        $pedido->lineasTareas()->whereNotIn('id', $existingTareaIds)->delete();

        $detalles = $request->input('existing_tarea_detalles', []);
        foreach ($pedido->lineasTareas()->get() as $tarea) {
          $pos = array_search($tarea->id, $existingTareaIds);
          if (array_key_exists($pos, $detalles) && (string) $detalles[$pos] !== (string) $tarea->detalles) {
            $tarea->update(['detalles' => $detalles[$pos]]);
          }
        }

        // 5. Nuevas tareas
        $maxLinea = (int) $pedido->lineasTareas()->max('linea');
        foreach ($request->input('new_tareas', []) as $index => $tareaId) {
          DistribucionLineaTareas::create([
            'pedido_id'       => $pedido->id,
            'distribucion_id' => $pedido->distribucion_id,
            'fecha'           => $pedido->fecha,
            'fechaEntrega'    => $pedido->fechaEntrega,
            'linea'           => ++$maxLinea,
            'tarea_id'        => $tareaId,
            'detalles'        => $request->input('new_tarea_detalles', [])[$index] ?? null,
            'status'          => 'A',
          ]);
        }

        // Las líneas acompañan la fecha de entrega del pedido
        $pedido->lineasPedidos()->update(['fechaEntrega' => $pedido->fechaEntrega]);
        $pedido->lineasTareas()->update(['fechaEntrega' => $pedido->fechaEntrega]);

        // 6. Recalcular total y tipo del pedido
        $tieneProductos = $lineas->isNotEmpty();
        $tieneTareas    = $pedido->lineasTareas()->exists();
        $pedido->totalPedido = round($lineas->sum('totalLinea'), 2);
        $pedido->tipo = match (true) {
          $tieneProductos && $tieneTareas => 'PT',
          $tieneProductos => 'P',
          $tieneTareas => 'T',
          default => null,
        };
        $pedido->save();
        $this->actualizarUltimaEntrega($pedido);

        return $pedido;
      });
    } catch (\Exception $e) {
      report($e);
      return redirect()->back()->withInput()->with('error', 'Error al actualizar el pedido. Intente nuevamente o contacte al administrador.');
    }

    // Vuelve al día de Reparto desde el que se abrió (aunque se haya cambiado la fecha de entrega)
    return redirect()->route('distribucion_reparto.index', ['fecha' => $request->input('volver') ?: $pedido->fechaEntrega])
      ->with('success', 'Pedido ' . $pedido->id . ' actualizado correctamente.');
  }

  /**
   * Baja lógica (status 'D', igual que el sistema anterior): sale del reparto, las impresiones y
   * los totales, pero el pedido y sus líneas quedan en la base y la baja en la auditoría.
   */
  public function destroy(Request $request, $id)
  {
    $request->validate(['volver' => 'nullable|date_format:Y-m-d']);

    $pedido = DistribucionNroPedidos::findOrFail($id);
    $pedido->update(['status' => 'D']);

    return redirect()->route('distribucion_reparto.index', ['fecha' => $request->input('volver') ?: $pedido->fechaEntrega])
      ->with('success', 'Pedido ' . $pedido->id . ' eliminado.');
  }

  /**
   * Productos que compra el cliente (con precio, IVA y fechas) y tareas, para armar el pedido.
   */
  public function getProductosTareas($distribucion_id)
  {
    $productos = DistribucionProducto::join('productos_c_d_a', 'distribucion_productos.producto_id', '=', 'productos_c_d_a.id')
      ->where('distribucion_productos.distribucion_id', $distribucion_id)
      ->where('distribucion_productos.status', 'A')
      ->where('productos_c_d_a.status', 'A')
      ->orderBy('productos_c_d_a.productoCDA')
      ->get([
        'distribucion_productos.producto_id', 'productos_c_d_a.productoCDA as nombre', 'productos_c_d_a.ivacda',
        'distribucion_productos.precio', 'distribucion_productos.fecha', 'distribucion_productos.fechaUEnt',
      ])
      ->map(fn($p) => [
        'producto_id' => $p->producto_id,
        'nombre' => trim((string) $p->nombre) !== '' ? $p->nombre : '(sin dato)',
        'ivacda' => $p->ivacda,
        'precio' => $p->precio,
        'fecha' => $p->fecha ? fecha($p->fecha) : '',
        'fechaUEnt' => $p->fechaUEnt ? fecha($p->fechaUEnt) : '',
      ]);

    $tareas = DistribucionTareas::where('status', 'A')->get(['id', 'tarea']);

    return response()->json(['productos' => $productos, 'tareas' => $tareas]);
  }

  /** Productos del catálogo que se le pueden agregar a un cliente. */
  private function catalogo()
  {
    return productoCDA::where('status', 'A')->orderBy('productoCDA')->get();
  }

  /**
   * Fecha de última entrega de los productos del cliente, como el sistema anterior al confirmar el pedido:
   * no cuentan las líneas sin cargo ni a retirar, y nunca retrocede.
   */
  private function actualizarUltimaEntrega(DistribucionNroPedidos $pedido): void
  {
    $entregados = $pedido->lineasPedidos()->get()
      ->reject(fn($l) => DistribucionLineaPedidos::marcado($l->cambiar) || DistribucionLineaPedidos::marcado($l->retirar))
      ->pluck('producto_id');

    DistribucionProducto::where('distribucion_id', $pedido->distribucion_id)
      ->where('status', 'A')
      ->whereIn('producto_id', $entregados)
      ->where(fn($q) => $q->whereNull('fechaUEnt')->orWhere('fechaUEnt', '<', $pedido->fechaEntrega))
      ->get()
      ->each->update(['fechaUEnt' => $pedido->fechaEntrega]);
  }

  /**
   * Productos asignados a un cliente, con su producto CDA, indexados por producto_id.
   */
  protected function productosDelCliente($clienteId)
  {
    // Los activos al final: si el producto está repetido (uno dado de baja), keyBy se queda con el activo
    return DistribucionProducto::with('producto')
      ->where('distribucion_id', $clienteId)
      ->orderByRaw("CASE WHEN status = 'A' THEN 1 ELSE 0 END")
      ->get()
      ->keyBy('producto_id');
  }

  /**
   * Alícuota de IVA de un producto del cliente (21% si no se encuentra).
   */
  protected function ivaDe(?DistribucionProducto $dp): float
  {
    return $dp?->producto?->porcentajeIva() ?? 21.0;
  }
}
