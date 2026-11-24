<?php

namespace App\Http\Controllers;

use App\Models\productoCDA;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Distribuciones → Productos: catálogo del que se eligen los productos de cada cliente. La baja es lógica.
 */
class DistribucionCatalogoController extends Controller
{
  public function index(Request $request)
  {
    $name = trim((string) $request->get('name'));
    $productos = productoCDA::where('status', 'A')
      ->when($name !== '', fn($q) => $q->where('productoCDA', 'like', "%$name%"))
      ->withCount(['distribuciones as clientes' => fn($q) => $q->where('status', 'A')])
      ->orderBy('productoCDA')
      ->paginate(25)
      ->withQueryString();

    return view('pages.Distribucion.Catalogo.index', compact('productos', 'name'));
  }

  public function create()
  {
    return view('pages.Distribucion.Catalogo.form', ['producto' => new productoCDA(['ivacda' => '21'])]);
  }

  public function store(Request $request)
  {
    productoCDA::create($this->datos($request) + ['status' => 'A']);
    return redirect()->route('distribucion_catalogo.index')->with('success', 'Producto creado correctamente.');
  }

  public function edit($id)
  {
    return view('pages.Distribucion.Catalogo.form', ['producto' => productoCDA::where('status', 'A')->findOrFail($id)]);
  }

  public function update(Request $request, $id)
  {
    $producto = productoCDA::where('status', 'A')->findOrFail($id);
    $producto->update($this->datos($request, $producto));
    return redirect()->route('distribucion_catalogo.index')->with('success', 'Producto actualizado correctamente.');
  }

  public function destroy($id)
  {
    productoCDA::findOrFail($id)->update(['status' => 'D']);
    return redirect()->route('distribucion_catalogo.index')->with('success', 'Producto eliminado del catálogo.');
  }

  private function datos(Request $request, ?productoCDA $producto = null): array
  {
    $datos = $request->validate([
      'productoCDA' => ['required', 'string', 'max:150', Rule::unique('productos_c_d_a', 'productoCDA')->where('status', 'A')->ignore($producto?->id)],
      'ivacda' => 'required|in:10.5,21',
    ], [], ['productoCDA' => 'producto', 'ivacda' => 'IVA']);

    return $datos + ['ivancda' => (float) $datos['ivacda']];
  }
}
