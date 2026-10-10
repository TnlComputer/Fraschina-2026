<div class="row">
  <div class="col-md-6 mb-3">
    <label for="nombre" class="form-label">Nombre</label>
    <input type="text" name="nombre" id="nombre" class="form-control @error('nombre') is-invalid @enderror"
      value="{{ old('nombre', $personal->nombre ?? '') }}" required>
    @error('nombre')
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-6 mb-3">
    <label for="apellido" class="form-label">Apellido</label>
    <input type="text" name="apellido" id="apellido" class="form-control @error('apellido') is-invalid @enderror"
      value="{{ old('apellido', $personal->apellido ?? '') }}">
    @error('apellido')
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

</div>

<div class="row">
  <div class="col-md-12 mb-3">
    <label for="transporte_id" class="form-label">Transporte</label>
    <select name="transporte_id" id="transporte_id" class="form-control @error('transporte_id') is-invalid @enderror" required>
      <option value="">Seleccione un Transporte</option>
      @foreach ($transportes as $transporte)
      <option value="{{ $transporte->id }}" {{ old('transporte_id', $personal->transporte_id ?? '') ==
        $transporte->id ? 'selected' : '' }}>
        {{ $transporte->razonsocial }}
      </option>
      @endforeach
    </select>
    @error('transporte_id')
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-6 mb-3">
    <label for="area_id" class="form-label">Área</label>
    <select name="area_id" id="area_id" class="form-control @error('area_id') is-invalid @enderror" required>
      <option value="">Seleccione un área</option>
      @foreach ($areas as $area)
      <option value="{{ $area->id }}" {{ old('area_id', $personal->area_id ?? '') == $area->id ? 'selected'
        : '' }}>
        {{ sin_dato($area->area) }}
      </option>
      @endforeach
    </select>
    @error('area_id')
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>
  {{--
</div> --}}

{{-- <div class="row"> --}}
  <div class="col-md-6 mb-3">
    <label for="cargo_id" class="form-label">Cargo</label>
    <select name="cargo_id" id="cargo_id" class="form-control @error('cargo_id') is-invalid @enderror" required>
      <option value="">Seleccione un cargo</option>
      @foreach ($cargos as $cargo)
      <option value="{{ $cargo->id }}" {{ old('cargo_id', $personal->cargo_id ?? '') == $cargo->id ?
        'selected' : '' }}>
        {{ sin_dato($cargo->cargo) }}
      </option>
      @endforeach
    </select>
    @error('cargo_id')
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-6 mb-3">
    <label for="profesion_id" class="form-label">Profesión</label>
    <select name="profesion_id" id="profesion_id" class="form-control @error('profesion_id') is-invalid @enderror"
      required>
      <option value="">Seleccione una profesión</option>
      @foreach ($profesiones as $profesion)
      <option value="{{ $profesion->id }}" {{ old('profesion_id', $personal->profesion_id ?? '') ==
        $profesion->id ? 'selected' : '' }}>
        {{ sin_dato($profesion->nombreprofesion) }}
      </option>
      @endforeach
    </select>
    @error('profesion_id')
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>
  {{--
</div>

<div class="row"> --}}
  <div class="col-md-6 mb-3">
    <label for="teldirecto" class="form-label">Teléfono Directo</label>
    <input type="text" name="teldirecto" id="teldirecto" class="form-control @error('teldirecto') is-invalid @enderror"
      value="{{ old('teldirecto', $personal->teldirecto ?? '') }}">
    @error('teldirecto')
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-6">
    <div class="form-group">
      <label for="interno">Interno</label>
      <input type="text" name="interno" id="interno" class="form-control @error('interno') is-invalid @enderror"
        value="{{ old('interno', $personal->interno ?? '') }}">
      @error('interno')
      <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>
  </div>
  {{--
</div>

<div class="row"> --}}
  <div class="col-md-6">
    <div class="form-group">
      <label for="telcelular">Celular</label>
      <input type="text" name="telcelular" id="telcelular"
        class="form-control @error('telcelular') is-invalid @enderror"
        value="{{ old('telcelular', $personal->telcelular ?? '') }}">
      @error('telcelular')
      <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>
  </div>
  {{--
</div> --}}

<div class="col-md-6 mb-3">
  <label for="telparticular" class="form-label">Teléfono Particular</label>
  <input type="text" name="telparticular" id="telparticular"
    class="form-control @error('telparticular') is-invalid @enderror"
    value="{{ old('telparticular', $personal->telparticular ?? '') }}">
  @error('telparticular')
  <div class="invalid-feedback">{{ $message }}</div>
  @enderror
</div>

<div class="col-md-6 mb-3">
  <label for="email" class="form-label">Email</label>
  <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
    value="{{ old('email', $personal->email ?? '') }}">
  @error('email')
  <div class="invalid-feedback">{{ $message }}</div>
  @enderror
</div>

{{-- <div class="row"> --}}
  <div class="col-12 mb-3">
    <label for="observaciones" class="form-label">Observaciones</label>
    <textarea name="observaciones" id="observaciones"
      class="form-control @error('observaciones') is-invalid @enderror">{{ old('observaciones', $personal->observaciones ?? '') }}</textarea>
    @error('observaciones')
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  <!-- Fuera (checkbox) -->
  <div class="col-md-6 mb-3">
    <label for="fuera" class="form-label">Ya no está en la empresa</label>
    <input type="checkbox" name="fuera" id="fuera" class="" {{ old('fuera', $personal->fuera) == 1 ?
    'checked' : '' }}>
    @error('fuera')
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>
</div>
<div class="row">
  <div>
    @foreach ($errors->all() as $error)
    <div class="alert alert-danger">{{ $error }}</div>
    @endforeach
  </div>
</div>
</div>