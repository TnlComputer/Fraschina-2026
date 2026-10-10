@extends('adminlte::page')

@section('title', 'Configuración cliente-molino')

@section('content_header')
<h1>{{ $config->exists ? 'Editar' : 'Nueva' }} configuración cliente-molino</h1>
@endsection

@section('content')
<form method="POST" action="{{ $config->exists ? route('expedicion_MTC.update', $config) : route('expedicion_MTC.store') }}">
  @csrf
  @if ($config->exists) @method('PUT') @endif
  <div class="card">
    <div class="card-body">
      <div class="form-row">
        <div class="col-md-4">
          <label>Cliente *</label>
          <select name="expClie_id" class="form-control" required>
            <option value="">Seleccione</option>
            @foreach ($clientes as $c)
            <option value="{{ $c->id }}" @selected(old('expClie_id', $config->expClie_id ?? request('cliente_id')) == $c->id)>{{ $c->nombre }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-4">
          <label>Molino *</label>
          <select name="expMolino_id" class="form-control" required>
            <option value="">Seleccione</option>
            @foreach ($molinos as $m)
            <option value="{{ $m->id }}" @selected(old('expMolino_id', $config->expMolino_id) == $m->id)>{{ $m->nombre }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <h5 class="mt-4">Condiciones habituales</h5>
      <p class="small text-muted">Se precargan al pasar un pedido con esta combinación y se pueden cambiar en cada pedido.</p>
      <div class="form-row">
        <div class="col-md-2">
          <label>Plazo (días)</label>
          <input type="number" min="0" name="plazo_dias" value="{{ old('plazo_dias', $config->plazo_dias) }}" class="form-control">
        </div>
        <div class="col-md-4">
          <label>Forma de pago</label>
          <input type="text" name="forma_pago" value="{{ old('forma_pago', $config->forma_pago) }}" class="form-control" maxlength="100">
        </div>
        <div class="col-md-3">
          <label>Condición</label>
          <input type="text" name="condicion" value="{{ old('condicion', $config->condicion) }}" class="form-control" maxlength="100">
        </div>
        <div class="col-md-3">
          <label>Planta</label>
          <input type="text" name="planta" value="{{ old('planta', $config->planta) }}" class="form-control" maxlength="100">
        </div>
      </div>

      <h5 class="mt-4">Qué incluye la planilla de pedido</h5>
      @php $precio = old('mostrar_precio', $config->mostrar_precio === null ? '' : (int) $config->mostrar_precio); @endphp
      <div class="form-group" style="max-width: 320px">
        <label class="mb-0">Precio</label>
        <select name="mostrar_precio" class="form-control form-control-sm">
          <option value="" @selected($precio === '')>Según el molino</option>
          <option value="1" @selected((string) $precio === '1')>Mostrar</option>
          <option value="0" @selected((string) $precio === '0')>No mostrar</option>
        </select>
      </div>
      @foreach ([
        'subrayar_precio' => 'Subrayar el precio',
        'mostrar_fecha_alternativa' => 'Incluir la leyenda de fecha alternativa',
        'mostrar_especiales' => 'Incluir observaciones especiales',
      ] as $campo => $texto)
      <div class="form-check">
        <input type="checkbox" name="{{ $campo }}" value="1" id="{{ $campo }}" class="form-check-input" @checked(old($campo, $config->$campo))>
        <label for="{{ $campo }}" class="form-check-label">{{ $texto }}</label>
      </div>
      @endforeach

      <h5 class="mt-4">Textos al pie para este molino</h5>
      <p class="small text-muted mb-1">Si se completan, reemplazan a los textos generales del cliente.</p>
      @foreach (range(1, 6) as $i)
      <input type="text" name="linea{{ $i }}" value="{{ old('linea' . $i, $config->{'linea' . $i}) }}" class="form-control form-control-sm mb-1" maxlength="200" placeholder="Línea {{ $i }}">
      @endforeach
    </div>
    <div class="card-footer form-acciones">
      <button class="btn btn-primary">Guardar</button>
      <a href="{{ route('expedicion_MTC.index') }}" class="btn btn-outline-secondary">Cancelar</a>
    </div>
  </div>
</form>
@endsection
