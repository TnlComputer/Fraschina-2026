@extends('adminlte::page')

@php
$editando = $registro->exists;
$titulo = ($editando ? 'Editar ' : (($cfg['femenino'] ?? false) ? 'Nueva ' : 'Nuevo ')) . mb_strtolower($cfg['titulo']);
@endphp

@section('title', $titulo)

@section('content_header')
<h1>{{ $titulo }}</h1>
@endsection

@section('content')
<form method="POST" action="{{ $editando ? route($cfg['ruta'] . '.update', $registro->getKey()) : route($cfg['ruta'] . '.store') }}">
  @csrf
  @if ($editando) @method('PUT') @endif

  <div class="card" style="max-width: 760px">
    <div class="card-body">
      @foreach ($cfg['campos'] as $columna => $campo)
      @php $tipo = $campo['tipo'] ?? 'text'; @endphp

      @if ($tipo === 'usos')
      <div class="form-group">
        <label class="d-block">{{ $campo['label'] }}</label>
        @foreach ($usos as $uso => $nombre)
        @if (array_key_exists($uso, $registro->getAttributes()) || !$editando)
        <div class="form-check form-check-inline">
          <input type="checkbox" class="form-check-input" id="uso-{{ $uso }}" name="usos[{{ $uso }}]" value="1"
            @checked(old("usos.$uso", \App\Models\Distribucion::esSi($registro->getAttribute($uso))))>
          <label class="form-check-label font-weight-normal" for="uso-{{ $uso }}">{{ $nombre }}</label>
        </div>
        @endif
        @endforeach
      </div>

      @elseif ($tipo === 'color')
      <div class="form-group">
        <label for="{{ $columna }}">{{ $campo['label'] }}</label>
        <select name="{{ $columna }}" id="{{ $columna }}" class="form-control @error($columna) is-invalid @enderror" style="max-width: 260px">
          <option value="">Sin color</option>
          @foreach ($colores as $color)
          <option value="{{ $color }}" style="background: {{ color_css($color) }}" @selected(old($columna, $registro->getAttribute($columna)) === $color)>{{ $color }}</option>
          @endforeach
          @php $actual = $registro->getAttribute($columna); @endphp
          @if ($actual && !in_array($actual, $colores))
          <option value="{{ $actual }}" selected>{{ $actual }}</option>
          @endif
        </select>
        @error($columna)<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>

      @else
      <div class="form-group">
        <label for="{{ $columna }}">{{ $campo['label'] }}@if ($campo['requerido'] ?? false) <span class="text-danger">*</span>@endif</label>
        <input type="{{ $tipo === 'number' ? 'number' : ($tipo === 'time' ? 'time' : 'text') }}" name="{{ $columna }}" id="{{ $columna }}"
          value="{{ old($columna, $tipo === 'time' ? hora($registro->getAttribute($columna)) : $registro->getAttribute($columna)) }}"
          class="form-control @error($columna) is-invalid @enderror" @if ($tipo === 'text') maxlength="{{ $campo['max'] ?? 150 }}" @endif
          @if ($campo['requerido'] ?? false) required @endif @if ($loop->first) autofocus @endif
          @if ($tipo !== 'text') style="max-width: 220px" @endif>
        @error($columna)<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      @endif
      @endforeach
    </div>
    <div class="card-footer form-acciones">
      <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
      <a href="{{ route($cfg['ruta'] . '.index') }}" class="btn btn-outline-secondary">Cancelar</a>
    </div>
  </div>
</form>
@endsection
