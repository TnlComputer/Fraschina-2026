{{-- Campos del personal de representación (alta y edición), como en el sistema anterior --}}
@php $personal = $personal ?? new \App\Models\RepresentacionPersonal(); @endphp
<input type="hidden" name="representacion_id" value="{{ old('representacion_id', $personal->representacion_id ?? $representacion->id) }}">

<div class="row">
  <div class="col-md-6 form-group">
    <label for="nombre">Nombre <span class="text-danger">*</span></label>
    <input type="text" name="nombre" id="nombre" class="form-control @error('nombre') is-invalid @enderror"
      value="{{ old('nombre', $personal->nombre) }}" maxlength="150" required autofocus>
    @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <div class="col-md-6 form-group">
    <label for="apellido">Apellido <span class="text-danger">*</span></label>
    <input type="text" name="apellido" id="apellido" class="form-control @error('apellido') is-invalid @enderror"
      value="{{ old('apellido', $personal->apellido) }}" maxlength="150" required>
    @error('apellido')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>

  <div class="col-md-4 form-group">
    <label for="area_id">Área</label>
    <select name="area_id" id="area_id" class="form-control @error('area_id') is-invalid @enderror">
      <option value="">Seleccione un área</option>
      @foreach ($areas as $area)
      <option value="{{ $area->id }}" @selected(old('area_id', $personal->area_id) == $area->id)>{{ sin_dato($area->area) }}</option>
      @endforeach
    </select>
    @error('area_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <div class="col-md-4 form-group">
    <label for="cargo_id">Cargo</label>
    <select name="cargo_id" id="cargo_id" class="form-control @error('cargo_id') is-invalid @enderror">
      <option value="">Seleccione un cargo</option>
      @foreach ($cargos as $cargo)
      <option value="{{ $cargo->id }}" @selected(old('cargo_id', $personal->cargo_id) == $cargo->id)>{{ sin_dato($cargo->cargo) }}</option>
      @endforeach
    </select>
    @error('cargo_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <div class="col-md-4 form-group">
    <label for="profesion_id">Profesión</label>
    <select name="profesion_id" id="profesion_id" class="form-control @error('profesion_id') is-invalid @enderror">
      <option value="">Seleccione una profesión</option>
      @foreach ($profesiones as $profesion)
      <option value="{{ $profesion->id }}" @selected(old('profesion_id', $personal->profesion_id) == $profesion->id)>{{ sin_dato($profesion->nombreprofesion) }}</option>
      @endforeach
    </select>
    @error('profesion_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>

  <div class="col-md-3 form-group">
    <label for="teldirecto">Teléfono directo</label>
    <input type="text" name="teldirecto" id="teldirecto" class="form-control @error('teldirecto') is-invalid @enderror"
      value="{{ old('teldirecto', $personal->teldirecto) }}" maxlength="50">
    @error('teldirecto')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <div class="col-md-2 form-group">
    <label for="interno">Interno</label>
    <input type="text" name="interno" id="interno" class="form-control @error('interno') is-invalid @enderror"
      value="{{ old('interno', $personal->interno) }}" maxlength="50">
    @error('interno')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <div class="col-md-3 form-group">
    <label for="telcelular">Celular</label>
    <input type="text" name="telcelular" id="telcelular" class="form-control @error('telcelular') is-invalid @enderror"
      value="{{ old('telcelular', $personal->telcelular) }}" maxlength="50">
    @error('telcelular')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <div class="col-md-4 form-group">
    <label for="telparticular">Teléfono particular</label>
    <input type="text" name="telparticular" id="telparticular" class="form-control @error('telparticular') is-invalid @enderror"
      value="{{ old('telparticular', $personal->telparticular) }}" maxlength="50">
    @error('telparticular')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>

  <div class="col-md-8 form-group">
    <label for="email">Email</label>
    <input type="text" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
      value="{{ old('email', $personal->email) }}" maxlength="150">
    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <div class="col-md-4 form-group">
    <label class="d-block">Situación</label>
    <input type="hidden" name="fuera" value="0">
    <div class="form-check mt-2">
      <input type="checkbox" class="form-check-input" name="fuera" id="fuera" value="1" @checked((int) old('fuera', $personal->fuera ?? 0) === 1)>
      <label class="form-check-label font-weight-normal" for="fuera">Ya no está en la empresa</label>
    </div>
  </div>

  <div class="col-12 form-group">
    <label for="observaciones">Información particular</label>
    <textarea name="observaciones" id="observaciones" rows="3" class="form-control @error('observaciones') is-invalid @enderror">{{ old('observaciones', $personal->observaciones) }}</textarea>
    @error('observaciones')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
</div>
