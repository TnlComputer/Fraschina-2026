@extends('adminlte::page')

@section('title', 'Productos de Distribución')

@section('content_header')
<h1>Productos de Distribución</h1>
@endsection

@section('content')
<div class="d-flex justify-content-between mb-3">
  <div>
    @can('permiso_11')<a href="{{ route('distribucion_catalogo.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Nuevo producto</a>@endcan
  </div>
  <form method="GET" action="{{ route('distribucion_catalogo.index') }}" class="form__buscar d-flex">
    <input type="text" name="name" class="form-control mr-1" placeholder="Buscar producto" value="{{ $name }}">
    <button class="btn btn-secondary" type="submit"><i class="fas fa-search"></i></button>
  </form>
</div>

<div class="card">
  <div class="card-body p-0">
    <table class="table table-sm table-striped table-bordered mb-0">
      <thead>
        <tr>
          <th>Producto</th>
          <th class="text-center">IVA</th>
          <th class="text-center">Clientes que lo compran</th>
          <th class="text-center">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($productos as $producto)
        <tr>
          <td>{{ $producto->nombre() }}</td>
          <td class="text-center">{{ numero($producto->porcentajeIva(), 1) }} %</td>
          <td class="text-center">{{ $producto->clientes }}</td>
          <td class="d-flex justify-content-center">
            @can('permiso_12')
            <a href="{{ route('distribucion_catalogo.edit', $producto->id) }}" class="btn-xs btn-warning mx-1" title="Editar">
              <i class="fa-solid fa-pen-to-square fa-xs"></i>
            </a>
            @endcan
            @can('permiso_13')
            <form method="POST" action="{{ route('distribucion_catalogo.destroy', $producto->id) }}"
              onsubmit="return confirm('¿Eliminar este producto del catálogo? Deja de ofrecerse en los pedidos{{ $producto->clientes ? ' (clientes que lo compran: ' . $producto->clientes . ')' : '' }}.');">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn-2xs btn-danger mx-1" title="Eliminar"><i class="fa-solid fa-trash fa-xs"></i></button>
            </form>
            @endcan
          </td>
        </tr>
        @empty
        <tr><td colspan="4" class="text-center">No hay registros para mostrar...</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
{{ $productos->links() }}
@endsection
