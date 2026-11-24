<?php

namespace App\Http\Controllers;

use App\Models\Distribucion;
use App\Models\DistribucionProducto;
use App\Models\productoCDA;
use Dompdf\Dompdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DistribucionProductoController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    //
  }

  /**
   * Show the form for creating a new resource.
   */

  public function create($distribucion_id)
  {
    // $productos = ProductoCDA::orderBy('productoCDA', 'ASC')->get(); // Obtener productos

    $productos = productoCDA::where('status', '=', 'A')->orderBy('productoCDA', 'asc')->get();

    return view('pages.Distribucion.Producto.create', compact('productos', 'distribucion_id'));
  }

  /**
   * Agrega un producto del catálogo a la lista del cliente (desde la ficha o, por JSON, desde el pedido).
   * Si el cliente ya lo había tenido y se le dio de baja, se reactiva con el precio nuevo.
   */
  public function store(Request $request)
  {
    $validated = $request->validate([
      'producto_id'     => ['nullable', 'required_without:producto_nuevo', Rule::exists('productos_c_d_a', 'id')->where('status', 'A')],
      // Producto que no está en el catálogo: se crea y queda en el catálogo y en la lista del cliente
      'producto_nuevo'  => ['nullable', 'string', 'max:150', Rule::unique('productos_c_d_a', 'productoCDA')->where('status', 'A')],
      'ivacda'          => 'nullable|required_with:producto_nuevo|in:10.5,21',
      'precio'          => 'required|numeric|min:0',
      'fecha'           => 'required|date',
      'fechaUEnt'       => 'nullable|date',
      'distribucion_id' => ['required', Rule::exists('distribucions', 'id')->where('status', 'A')],
    ], [
      'producto_nuevo.unique' => 'Ese producto ya está en el catálogo: búsquelo en la lista.',
      'producto_id.required_without' => 'Seleccione el producto.',
    ], ['producto_id' => 'producto', 'producto_nuevo' => 'producto nuevo', 'ivacda' => 'IVA']);

    $productoId = DB::transaction(function () use ($validated) {
      // Bloquea al cliente para que un doble clic no cargue el producto dos veces
      Distribucion::lockForUpdate()->findOrFail($validated['distribucion_id']);
      if (!empty($validated['producto_nuevo'])) {
        $validated['producto_id'] = productoCDA::create([
          'productoCDA' => $validated['producto_nuevo'],
          'ivacda' => $validated['ivacda'],
          'ivancda' => (float) $validated['ivacda'],
          'status' => 'A',
        ])->id;
      }
      unset($validated['producto_nuevo'], $validated['ivacda']);
      $this->exigirQueNoLoTenga($validated['distribucion_id'], $validated['producto_id']);

      $validated['status'] = 'A';
      $validated['nomproducto'] = productoCDA::findOrFail($validated['producto_id'])->productoCDA;
      DistribucionProducto::updateOrCreate(
        ['distribucion_id' => $validated['distribucion_id'], 'producto_id' => $validated['producto_id']],
        $validated
      );

      return $validated['producto_id'];
    });

    if ($request->expectsJson()) {
      return response()->json(['ok' => true, 'producto_id' => $productoId], 201);
    }

    return redirect()->route('distribucion.show', ['distribucion' => $validated['distribucion_id']])
      ->with('success', 'Producto agregado a la distribución.');
  }

  /**
   * Lista de productos y precios del cliente en PDF.
   */
  public function imprimir($distribucion)
  {
    $cliente = Distribucion::findOrFail($distribucion);
    $productos = $cliente->productosActivos()->with('producto')->get()->sortBy(fn($p) => $p->producto?->productoCDA);

    $pdf = new Dompdf();
    $pdf->loadHtml(view('pages.Distribucion.Printer.productos', compact('cliente', 'productos'))->render());
    $pdf->setPaper('A4', 'portrait');
    $pdf->render();

    return response($pdf->output(), 200, [
      'Content-Type' => 'application/pdf',
      'Content-Disposition' => 'inline; filename="Productos-cliente-' . $cliente->id . '.pdf"',
    ]);
  }

  private function exigirQueNoLoTenga($clienteId, $productoId, $exceptoId = null): void
  {
    $yaLoTiene = DistribucionProducto::where('distribucion_id', $clienteId)
      ->where('producto_id', $productoId)
      ->where('status', 'A')
      ->when($exceptoId, fn($q) => $q->whereKeyNot($exceptoId))
      ->exists();

    if ($yaLoTiene) {
      throw ValidationException::withMessages(['producto_id' => 'El cliente ya tiene ese producto en su lista.']);
    }
  }

  public function edit(DistribucionProducto $distribucionProducto)
  {
    $distribucion_producto = DistribucionProducto::findOrFail($distribucionProducto->id);

    $productos = productoCDA::where('status', '=', 'A')->orderBy('productoCDA', 'asc')->get();

    return view('pages.Distribucion.Producto.edit', compact('distribucion_producto', 'productos'));
  }

  public function update(Request $request, $id)
  {
    $validated = $request->validate([
      'producto_id' => 'required|exists:productos_c_d_a,id', // Asegura que existe en la tabla
      'precio'      => 'required|numeric|min:0',
      'fecha'       => 'required|date',
      'fechaUEnt'   => 'nullable|date',
      'status'      => 'required|in:A,D', // Validar que el estado sea 'A' o 'D'
    ]);

    $distribucion_producto = DistribucionProducto::findOrFail($id);
    if ((int) $validated['producto_id'] !== (int) $distribucion_producto->producto_id) {
      $this->exigirQueNoLoTenga($distribucion_producto->distribucion_id, $validated['producto_id'], $id);
      $validated['nomproducto'] = productoCDA::findOrFail($validated['producto_id'])->productoCDA;
    }
    $distribucion_producto->update($validated);

    return redirect()->route('distribucion.show', ['distribucion' => $distribucion_producto->distribucion_id])
      ->with('success', 'Producto actualizado en la distribución.');
  }

  /**
   * Display the specified resource.
   */
  public function show(string $id)
  {
    //
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy($id)
  {
    // dd($id);

    // Buscar el producto en la distribución
    $producto = DistribucionProducto::findOrFail($id);

    // Cambiar el estado a 'D' (desactivado)
    $producto->update(['status' => 'D']);

    // Redirigir con un mensaje de éxito
    return redirect()->route('distribucion.show', ['distribucion' => $producto->distribucion_id])
      ->with('success', 'Producto desactivado de la distribución.');
  }
}