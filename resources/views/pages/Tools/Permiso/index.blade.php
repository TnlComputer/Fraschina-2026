@extends('adminlte::page')

@section('title', 'Permisos')

@section('content_header')
<h1>Permisos</h1>
@endsection

@section('content')
<p class="text-muted mb-2">
  Los permisos los define el sistema. Para decidir qué puede hacer cada usuario, se asignan a los
  <a href="{{ route('roles.index') }}">roles</a>. Acá solo se cambia el nombre con el que se muestran.
</p>

@foreach ($grupos as $grupo => [$ayuda, $permisos])
<div class="card">
  <div class="card-header py-2">
    <h3 class="card-title font-weight-bold">{{ $grupo }} <small class="text-muted font-weight-normal ml-1">{{ $ayuda }}</small></h3>
  </div>
  <div class="card-body p-0 table-responsive">
    <table class="table table-sm table-hover mb-0">
      <thead>
        <tr>
          <th style="width: 22%">Nombre</th>
          <th style="width: 12%">Código</th>
          <th>Roles que lo tienen</th>
          <th class="text-center" style="width: 8%">Usuarios</th>
          <th style="width: 1%"></th>
        </tr>
      </thead>
      <tbody>
        @foreach ($permisos as $permiso)
        <tr>
          <td class="font-weight-bold">{{ $permiso->description }}</td>
          <td><code>{{ $permiso->name }}</code></td>
          <td>
            @forelse ($permiso->roles as $rol)
            <span class="badge badge-light border">{{ $rol->name }}</span>
            @empty
            <span class="text-muted">Ningún rol</span>
            @endforelse
            @if ($permiso->name !== \App\Http\Controllers\RoleController::SUPER)
            <small class="text-muted ml-1" title="El Super Usuario tiene todos los permisos">+ Super Usuario</small>
            @endif
          </td>
          <td class="text-center">
            @php $usuarios = $permiso->roles->sum('users_count'); @endphp
            @if ($usuarios)<span class="badge badge-secondary">{{ $usuarios }}</span>@else<span class="text-muted">—</span>@endif
          </td>
          <td class="d-flex">
            <a href="{{ route('permissions.edit', $permiso) }}" class="btn-xs btn-warning" title="Cambiar el nombre"><i class="fa-solid fa-pen-to-square fa-xs"></i></a>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endforeach
@endsection
