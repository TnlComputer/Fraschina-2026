@extends('adminlte::page')

@section('title', 'Configuración')

@section('content_header')
<h1>Configuración</h1>
@endsection

@use('App\Services\Configuraciones')
@php
$modo = old('mail_modo', $c('mail.modo'));
$diasElegidos = collect(old('consulta_dias', $c('expedicion.consulta_dias') ?? []))->map(fn($d) => (int) $d);
$activas = old('_token') ? (bool) old('consultas_activas') : (bool) $c('expedicion.consultas_activas');
@endphp

@section('content')
<p class="text-muted mb-2">Parámetros de la app. Solo los modifican el Administrador, el Supervisor y Gerencia, y cada cambio queda en la auditoría.</p>

<form method="POST" action="{{ route('configuracion.update') }}">
  @csrf
  @method('PUT')

  <div class="card" style="max-width: 900px">
    <div class="card-body">

      {{-- Correos --}}
      <h5 class="form-seccion"><i class="fas fa-envelope"></i> Correos</h5>
      <div class="form-group">
        <label class="d-block">Modo de envío</label>
        @foreach ($modos as $valor => $texto)
        <div class="custom-control custom-radio">
          <input type="radio" class="custom-control-input" id="modo-{{ $valor }}" name="mail_modo" value="{{ $valor }}" @checked($modo === $valor)>
          <label class="custom-control-label font-weight-normal" for="modo-{{ $valor }}">{{ $texto }}</label>
        </div>
        @endforeach
        @error('mail_modo')<div class="text-danger small">{{ $message }}</div>@enderror
        <small class="form-text text-muted">
          Afecta todos los correos de la app: planillas y consultas a molinos, y el «Olvidé mi contraseña».
          @if ($correoServidor) Servidor de correo: {{ $correoServidor }}. @else Todavía no hay un servidor de correo configurado en el servidor (.env): aunque se elija enviar, quedan en el log. @endif
        </small>
      </div>
      <div class="form-row">
        <div class="form-group col-md-6" id="grupo-casilla">
          <label for="mail_casilla_prueba">Casilla de prueba</label>
          <input type="email" name="mail_casilla_prueba" id="mail_casilla_prueba" class="form-control @error('mail_casilla_prueba') is-invalid @enderror"
            value="{{ old('mail_casilla_prueba', $c('mail.casilla_prueba')) }}" placeholder="pruebas@example.com">
          @error('mail_casilla_prueba')<div class="invalid-feedback">{{ $message }}</div>@enderror
          <small class="form-text text-muted">En modo prueba, todos los correos llegan acá en lugar de a los destinatarios.</small>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group col-md-6">
          <label for="mail_remitente">Correo del remitente</label>
          <input type="email" name="mail_remitente" id="mail_remitente" class="form-control @error('mail_remitente') is-invalid @enderror"
            value="{{ old('mail_remitente', $c('expedicion.mail_remitente')) }}" required>
          @error('mail_remitente')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-group col-md-6">
          <label for="mail_remitente_nombre">Nombre del remitente</label>
          <input type="text" name="mail_remitente_nombre" id="mail_remitente_nombre" class="form-control @error('mail_remitente_nombre') is-invalid @enderror"
            value="{{ old('mail_remitente_nombre', $c('expedicion.mail_remitente_nombre')) }}" maxlength="100" required>
          @error('mail_remitente_nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
      </div>

      {{-- Consultas automáticas --}}
      <h5 class="form-seccion mt-3"><i class="fas fa-clock"></i> Consultas automáticas a molinos</h5>
      <div class="custom-control custom-switch mb-3">
        <input type="hidden" name="consultas_activas" value="0">
        <input type="checkbox" class="custom-control-input" id="consultas_activas" name="consultas_activas" value="1" @checked($activas)>
        <label class="custom-control-label font-weight-normal" for="consultas_activas">Enviar las consultas de entregas y cargas</label>
      </div>
      <div class="form-row">
        <div class="form-group col-6 col-md-3">
          <label for="consulta_manana">Mañana <small class="text-muted">(entregas del día)</small></label>
          <input type="time" name="consulta_manana" id="consulta_manana" class="form-control @error('consulta_manana') is-invalid @enderror"
            value="{{ old('consulta_manana', $c('expedicion.consulta_manana')) }}" required>
          @error('consulta_manana')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-group col-6 col-md-3">
          <label for="consulta_tarde">Tarde <small class="text-muted">(cargas del día siguiente)</small></label>
          <input type="time" name="consulta_tarde" id="consulta_tarde" class="form-control @error('consulta_tarde') is-invalid @enderror"
            value="{{ old('consulta_tarde', $c('expedicion.consulta_tarde')) }}" required>
          @error('consulta_tarde')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-group col-md-6">
          <label class="d-block">Días</label>
          @foreach ($dias as $numero => $nombre)
          <div class="custom-control custom-checkbox custom-control-inline">
            <input type="checkbox" class="custom-control-input" id="dia-{{ $numero }}" name="consulta_dias[]" value="{{ $numero }}" @checked($diasElegidos->contains($numero))>
            <label class="custom-control-label font-weight-normal" for="dia-{{ $numero }}">{{ mb_substr($nombre, 0, 3) }}</label>
          </div>
          @endforeach
          @error('consulta_dias')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
      </div>
      <small class="form-text text-muted mt-n2 mb-2">Solo se envían a las direcciones cargadas para cada molino en Expedición → Avisos.</small>

      {{-- Pedidos --}}
      <h5 class="form-seccion mt-3"><i class="fas fa-file-alt"></i> Pedidos de Expedición</h5>
      <div class="form-row">
        <div class="form-group col-md-4">
          <label for="numero_inicial">Número del primer pedido</label>
          <input type="number" name="numero_inicial" id="numero_inicial" min="1" class="form-control @error('numero_inicial') is-invalid @enderror"
            value="{{ old('numero_inicial', $c('expedicion.numero_inicial')) }}" @disabled($hayPedidos)>
          @error('numero_inicial')<div class="invalid-feedback">{{ $message }}</div>@enderror
          <small class="form-text text-muted">
            {{ $hayPedidos ? 'Ya hay pedidos: la numeración sigue sola.' : 'Para continuar la numeración del sistema anterior.' }}
          </small>
        </div>
        <div class="form-group col-md-5">
          <label for="nombre_planilla">Nombre de la planilla</label>
          <input type="text" name="nombre_planilla" id="nombre_planilla" class="form-control @error('nombre_planilla') is-invalid @enderror"
            value="{{ old('nombre_planilla', $c('expedicion.nombre_planilla')) }}" maxlength="100" required>
          @error('nombre_planilla')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-group col-md-3">
          <label for="respaldos_dias">Días de respaldo</label>
          <input type="number" name="respaldos_dias" id="respaldos_dias" min="1" max="365" class="form-control @error('respaldos_dias') is-invalid @enderror"
            value="{{ old('respaldos_dias', $c('expedicion.respaldos_dias')) }}" required>
          @error('respaldos_dias')<div class="invalid-feedback">{{ $message }}</div>@enderror
          <small class="form-text text-muted">Respaldos diarios en Excel que se conservan.</small>
        </div>
      </div>
    </div>
    <div class="card-footer form-acciones">
      <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
      <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">Cancelar</a>
    </div>
  </div>
</form>
@endsection

@push('js')
<script>
  // La casilla de prueba solo importa en modo prueba
  (function () {
    var grupo = document.getElementById('grupo-casilla');
    var actualizar = function () {
      var prueba = document.getElementById('modo-{{ Configuraciones::PRUEBA }}').checked;
      grupo.style.display = prueba ? '' : 'none';
      document.getElementById('mail_casilla_prueba').required = prueba;
    };
    document.querySelectorAll('input[name=mail_modo]').forEach(function (r) { r.addEventListener('change', actualizar); });
    actualizar();
  })();
</script>
@endpush
