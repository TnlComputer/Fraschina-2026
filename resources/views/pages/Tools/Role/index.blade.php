@extends('adminlte::page')

@section('title', 'Roles')

@section('content_header')
<div class="d-flex flex-wrap align-items-center justify-content-between">
  <h1>Roles</h1>
  @can('permiso_11')<a href="{{ route('roles.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Nuevo rol</a>@endcan
</div>
@endsection

@use('App\Http\Controllers\RoleController')
@php
$cols = collect(array_merge(RoleController::SECCIONES, RoleController::ACCIONES))->map(fn($n) => $permisos[$n] ?? null)->filter();
// rótulos cortos para que entren las columnas; el nombre completo queda en el title
$corto = ['permiso_1' => 'Repres.', 'permiso_2' => 'Distrib.', 'permiso_3' => 'Molino', 'permiso_4' => 'Agro', 'permiso_5' => 'Proveed.',
  'permiso_6' => 'Transp.', 'permiso_7' => 'Exped.', 'permiso_8' => 'Agenda', 'permiso_9' => 'Herram.', 'permiso_11' => 'Alta',
  'permiso_12' => 'Editar', 'permiso_13' => 'Eliminar'];
@endphp

@section('content')
<p class="text-muted mb-2">Cada usuario tiene un rol. El rol define a qué secciones entra y si puede dar de alta, modificar o eliminar registros.</p>

<div class="card">
  <div class="card-body p-0 table-responsive">
    <table class="table table-sm table-hover mb-0 tabla-roles">
      <thead>
        <tr>
          <th>Rol</th>
          <th class="text-center">Usuarios</th>
          @foreach ($cols as $p)
          <th class="text-center {{ in_array($p->name, RoleController::ACCIONES) ? 'col-accion' : '' }}" title="{{ $p->description }}">
            <span class="rotulo">{{ $corto[$p->name] ?? $p->description }}</span>
          </th>
          @endforeach
          <th></th>
        </tr>
      </thead>
      <tbody>
        @forelse ($roles as $role)
        @php $super = $role->permissions->contains('name', RoleController::SUPER); @endphp
        <tr>
          <td class="font-weight-bold text-nowrap">
            {{ $role->name }}
            @if ($super)<span class="badge badge-danger ml-1" title="Acceso total">Super Usuario</span>@endif
            @if (!$super && $role->permissions->contains('name', RoleController::CONFIGURACION))<span class="badge badge-info ml-1" title="Entra a Herramientas → Configuración">Configuración</span>@endif
          </td>
          <td class="text-center">
            @if ($role->users_count)
            <span class="badge badge-secondary">{{ $role->users_count }}</span>
            @else
            <span class="text-muted">—</span>
            @endif
          </td>
          @foreach ($cols as $p)
          <td class="text-center {{ in_array($p->name, RoleController::ACCIONES) ? 'col-accion' : '' }}">
            @if ($super || $role->permissions->contains('name', $p->name))
            <i class="fas fa-check text-success" title="{{ $p->description }}: sí"></i>
            @else
            <span class="text-muted">·</span>
            @endif
          </td>
          @endforeach
          <td class="d-flex">
            <a href="{{ route('roles.edit', $role) }}" class="btn-xs btn-warning" title="Editar"><i class="fa-solid fa-pen-to-square fa-xs"></i></a>
            @unless ($super)
            <form method="POST" action="{{ route('roles.destroy', $role) }}" onsubmit="return confirm('¿Eliminar el rol «{{ $role->name }}»?');">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn-xs btn-danger" title="{{ $role->users_count ? 'Tiene usuarios asignados' : 'Eliminar' }}" @disabled($role->users_count)>
                <i class="fa-solid fa-trash fa-xs"></i>
              </button>
            </form>
            @endunless
          </td>
        </tr>
        @empty
        <tr><td colspan="{{ $cols->count() + 3 }}" class="text-center text-muted">No hay roles.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection

@push('css')
<style>
  .tabla-roles th { vertical-align: bottom; font-size: .75rem; text-transform: none; letter-spacing: 0; padding: .4rem .3rem; }
  .tabla-roles th .rotulo { display: inline-block; max-width: 72px; white-space: normal; line-height: 1.15; overflow-wrap: normal; }
  .tabla-roles td { padding: .45rem .3rem; }
  .tabla-roles td, .tabla-roles th { vertical-align: middle; }
  .tabla-roles .col-accion { background: rgba(0, 123, 255, .05); }
  .tabla-roles button[disabled] { opacity: .35; cursor: not-allowed; }
</style>
@endpush
