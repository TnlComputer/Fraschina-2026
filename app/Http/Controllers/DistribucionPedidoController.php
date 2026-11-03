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
  public function create()
  {
    $clientes = Distribucion::where('status', 'A')
      ->orderBy('nomfantasia')
      ->get(['id', 'nomfantasia', 'razonsocial']);
    return view('pages.Distribucion.Pedido.create', compact('clientes'));
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

    // Validar todos los productos antes de escribir nada
    foreach ($productos as $index => $productoId) {
      if (in_array($tipos[$index] ?? null, ['P', 'PT']) && !$productosCliente->has($productoId)) {
        return back()->withInput()->withErrors(['error' => "El producto con ID $productoId no se encuentra en la distribución."]);
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
            'status' => 'A',
          ]);
        }
      }

      $pedido->update(['totalPedido' => round($totalPedido, 2)]);
    });

    return redirect()->route('distribucion_agenda.index')->with('success', 'Pedido creado correctamente');
  }

  public function getProductosYTareasPorCliente($clienteId)
  {
    // Obtener productos activos del cliente con status 'A' en ambas tablas
    $productos = DistribucionProducto::join('productos_c_d_a', 'distribucion_productos.producto_id', '=', 'productos_c_d_a.id')
      ->where('distribucion_productos.distribucion_id', $clienteId)
      ->where('distribucion_productos.status', 'A') // Filtra productos activos en DistribucionProducto
      ->where('productos_c_d_a.status', 'A') // Filtra productos activos en productos_c_d_a
      ->select('productos_c_d_a.id', 'productos_c_d_a.productoCDA as nombre', 'productos_c_d_a.ivacda', 'distribucion_productos.precio')
      ->get();

    // Obtener tareas activas con status 'A'
    $tareas = DistribucionTareas::where('status', 'A')
      ->select('id', 'tarea')
      ->get();

    return response()->json([
      'productos' => $productos,
      'tareas' => $tareas
    ]);
  }

  public function edit($id)
  {
    $pedido = DistribucionNroPedidos::with(['distribucion', 'lineasPedidos.producto', 'lineasTareas.tarea'])->findOrFail($id);

    return view('pages.Distribucion.Pedido.edit', compact('pedido'));
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
      'new_productos' => 'nullable|array',
      'new_productos.*' => 'integer',
      'new_precios' => 'nullable|array',
      'new_precios.*' => 'required|numeric|min:0',
      'new_cantidades' => 'nullable|array',
      'new_cantidades.*' => 'required|numeric|min:0',
      'existing_tarea_ids' => 'nullable|array',
      'existing_tarea_ids.*' => 'integer',
      'new_tareas' => 'nullable|array',
      'new_tareas.*' => 'integer|exists:distribucion_tareas,id',
    ]);

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
            $this->ivaDe($productosCliente->get($linea->producto_id))
          );
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
          $lineaExistente = $lineas->firstWhere('producto_id', $productoId);

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
              'status'          => 1,
            ]);
            $lineas->put($nueva->id, $nueva);
          }
        }

        // 4. Tareas existentes: conservar solo las que siguen en la vista
        $existingTareaIds = array_map('intval', $request->input('existing_tarea_ids', []));
        $pedido->lineasTareas()->whereNotIn('id', $existingTareaIds)->delete();

        // 5. Nuevas tareas
        $maxLinea = (int) $pedido->lineasTareas()->max('linea');
        foreach ($request->input('new_tareas', []) as $tareaId) {
          DistribucionLineaTareas::create([
            'pedido_id'       => $pedido->id,
            'distribucion_id' => $pedido->distribucion_id,
            'fecha'           => $pedido->fecha,
            'fechaEntrega'    => $pedido->fechaEntrega,
            'linea'           => ++$maxLinea,
            'tarea_id'        => $tareaId,
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

  public function getProductosTareas($distribucion_id)
  {
    // Productos activos del cliente con nombre, precio e IVA
    $productos = DistribucionProducto::join('productos_c_d_a', 'distribucion_productos.producto_id', '=', 'productos_c_d_a.id')
      ->where('distribucion_productos.distribucion_id', $distribucion_id)
      ->where('distribucion_productos.status', 'A')
      ->select('distribucion_productos.producto_id', 'productos_c_d_a.productoCDA as nombre', 'productos_c_d_a.ivacda', 'distribucion_productos.precio')
      ->orderBy('productos_c_d_a.productoCDA')
      ->get();

    // Tareas activas
    $tareas = DistribucionTareas::where('status', 'A')
      ->select('id', 'tarea')
      ->get();

    return response()->json([
      'productos' => $productos,
      'tareas' => $tareas
    ]);
  }

  /**
   * Productos asignados a un cliente, con su producto CDA, indexados por producto_id.
   */
  protected function productosDelCliente($clienteId)
  {
    return DistribucionProducto::with('producto')
      ->where('distribucion_id', $clienteId)
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
