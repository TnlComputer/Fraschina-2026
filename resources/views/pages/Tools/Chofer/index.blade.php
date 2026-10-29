@extends('adminlte::page')

@section('title', 'Choferes')

@section('content_header')
<h1>Choferes</h1>
@endsection

@section('content')
<p class="text-muted mb-2">El código es el que se escribe en el campo Chofer de Reparto.</p>

<div class="mb-3">
  @can('permiso_11')<a href="{{ route('choferes.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Nuevo chofer</a>@endcan
</div>

<div class="card" style="max-width: 760px">
  <div class="card-body p-0">
    <table class="table table-sm table-striped table-bordered mb-0">
      <thead>
        <tr>
          <th class="text-center">Código</th>
          <th>Nombre</th>
          <th>Teléfono</th>
          <th class="text-center">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($choferes as $chofer)
        <tr>
          <td class="text-center font-weight-bold">{{ $chofer->codigo }}</td>
          <td>{{ $chofer->nombre }}</td>
          <td>{{ $chofer->telefono ?: '—' }}</td>
          <td class="d-flex justify-content-center">
            @can('permiso_12')
            <a href="{{ route('choferes.edit', $chofer->id) }}" class="btn-xs btn-warning" title="Editar">
              <i class="fa-solid fa-pen-to-square fa-xs"></i>
            </a>
            @endcan
            @can('permiso_13')
            <form method="POST" action="{{ route('choferes.destroy', $chofer->id) }}"
              onsubmit="return confirm('¿Estás seguro de eliminar este chofer?');">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn-2xs btn-danger" title="Eliminar">
                <i class="fa-solid fa-trash fa-xs"></i>
              </button>
            </form>
            @endcan
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="4" class="text-center">Todavía no hay choferes cargados.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
