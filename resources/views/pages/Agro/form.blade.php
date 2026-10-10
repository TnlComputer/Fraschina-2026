<div class="row">
  <!-- Razón Social -->
  <div class="col-md-12 mb-3">
    <label for="razonsocial" class="form-label">Razón Social</label>
    <input type="text" class="form-control @error('razonsocial') is-invalid @enderror" id="razonsocial"
      name="razonsocial" value="{{ old('razonsocial', $agro->razonsocial ?? '') }}">
    @error('razonsocial')
    <span class="invalid-feedback" role="alert">
      <strong>{{ $message }}</strong>
    </span>
    @enderror
  </div>

  <!-- Calle -->
  <div class="col-md-4 mb-3">
    <label for="dire_calle" class="form-label">Calle</label>
    <input type="text" class="form-control @error('dire_calle') is-invalid @enderror" id="dire_calle" name="dire_calle"
      value="{{ old('dire_calle', $agro->dire_calle ?? '') }}">
    @error('dire_calle')
    <span class="invalid-feedback" role="alert">
      <strong>{{ $message }}</strong>
    </span>
    @enderror
  </div>

  <!-- Número -->
  <div class="col-md-1 mb-3">
    <label for="dire_nro" class="form-label">Número</label>
    <input type="text" class="form-control @error('dire_nro') is-invalid @enderror" id="dire_nro" name="dire_nro"
      value="{{ old('dire_nro', $agro->dire_nro ?? '') }}">
    @error('dire_nro')
    <span class="invalid-feedback" role="alert">
      <strong>{{ $message }}</strong>
    </span>
    @enderror
  </div>

  <!-- Piso -->
  <div class="col-md-1 mb-3">
    <label for="piso" class="form-label">Piso</label>
    <input type="text" class="form-control @error('piso') is-invalid @enderror" id="piso" name="piso"
      value="{{ old('piso', $agro->piso ?? '') }}">
    @error('piso')
    <span class="invalid-feedback" role="alert">
      <strong>{{ $message }}</strong>
    </span>
    @enderror
  </div>

  <!-- Departamento -->
  <div class="col-md-1 mb-3">
    <label for="dpto" class="form-label">Dpto</label>
    <input type="text" class="form-control @error('dpto') is-invalid @enderror" id="dpto" name="dpto"
      value="{{ old('dpto', $agro->dpto ?? '') }}">
    @error('dpto')
    <span class="invalid-feedback" role="alert">
      <strong>{{ $message }}</strong>
    </span>
    @enderror
  </div>

  <!-- Observación -->
  <div class="col-md-3 mb-3">
    <label for="dire_obs" class="form-label">Dirección Obs.</label>
    <input type="text" class="form-control @error('dire_obs') is-invalid @enderror" id="dire_obs" name="dire_obs"
      value="{{ old('dire_obs', $agro->dire_obs ?? '') }}">
    @error('dire_obs')
    <span class="invalid-feedback" role="alert">
      <strong>{{ $message }}</strong>
    </span>
    @enderror
  </div>

  <!-- Código Postal -->
  <div class="col-md-2 mb-3">
    <label for="codpost" class="form-label">Código Postal</label>
    <input type="text" class="form-control @error('codpost') is-invalid @enderror" id="codpost" name="codpost"
      value="{{ old('codpost', $agro->codpost ?? '') }}">
    @error('codpost')
    <span class="invalid-feedback" role="alert">
      <strong>{{ $message }}</strong>
    </span>
    @enderror
  </div>
  <!-- Barrio -->
  <div class="col-md-4 mb-3">
    <label for="barrio_id" class="form-label">Barrio</label>
    <select class="form-control @error('barrio_id') is-invalid @enderror" id="barrio_id" name="barrio_id">
      <option value="">Seleccione un barrio</option>
      @foreach($barrios as $barrio)
      <option value="{{ $barrio->id }}" {{ old('barrio_id', $agro->barrio_id ?? '') == $barrio->id ? 'selected' : ''
        }}>
        {{ sin_dato($barrio->nombrebarrio) }}
      </option>
      @endforeach
    </select>
    @error('barrio_id')
    <span class="invalid-feedback" role="alert">
      <strong>{{ $message }}</strong>
    </span>
    @enderror
  </div>

  <!-- Municipio -->
  <div class="col-md-4 mb-3">
    <label for="municipio_id" class="form-label">Municipio</label>
    <select class="form-control @error('municipio_id') is-invalid @enderror" id="municipio_id" name="municipio_id">
      <option value="">Seleccione un municipio</option>
      @foreach($municipios as $municipio)
      <option value="{{ $municipio->id }}" {{ old('municipio_id', $agro->municipio_id ?? '') == $municipio->id ?
        'selected' : '' }}>
        {{ sin_dato($municipio->ciudadmunicipio) }}
      </option>
      @endforeach
    </select>
    @error('municipio_id')
    <span class="invalid-feedback" role="alert">
      <strong>{{ $message }}</strong>
    </span>
    @enderror
  </div>

  <!-- Localidad -->
  <div class="col-md-4 mb-3">
    <label for="localidad_id" class="form-label">Localidad</label>
    <select class="form-control @error('localidad_id') is-invalid @enderror" id="localidad_id" name="localidad_id">
      <option value="">Seleccione una localidad</option>
      @foreach($localidades as $localidad)
      <option value="{{ $localidad->id }}" {{ old('localidad_id', $agro->localidad_id ?? '') == $localidad->id ?
        'selected' : '' }}>
        {{ sin_dato($localidad->localidad) }}
      </option>
      @endforeach
    </select>
    @error('localidad_id')
    <span class="invalid-feedback" role="alert">
      <strong>{{ $message }}</strong>
    </span>
    @enderror
  </div>

  <!-- Teléfono -->
  <div class="col-md-4 mb-3">
    <label for="telefono" class="form-label">Teléfono</label>
    <input type="text" class="form-control @error('telefono') is-invalid @enderror" id="telefono" name="telefono"
      value="{{ old('telefono', $agro->telefono ?? '') }}">
    @error('telefono')
    <span class="invalid-feedback" role="alert">
      <strong>{{ $message }}</strong>
    </span>
    @enderror
  </div>

  <!-- Fax -->
  <div class="col-md-4 mb-3">
    <label for="fax" class="form-label">Fax</label>
    <input type="text" class="form-control @error('fax') is-invalid @enderror" id="fax" name="fax"
      value="{{ old('fax', $agro->fax ?? '') }}">
    @error('fax')
    <span class="invalid-feedback" role="alert">
      <strong>{{ $message }}</strong>
    </span>
    @enderror
  </div>

  <!-- CUIT -->
  <div class="col-md-4 mb-3">
    <label for="cuit" class="form-label">CUIT</label>
    <input type="text" class="form-control @error('cuit') is-invalid @enderror" id="cuit" name="cuit"
      value="{{ old('cuit', $agro->cuit ?? '') }}">
    @error('cuit')
    <span class="invalid-feedback" role="alert">
      <strong>{{ $message }}</strong>
    </span>
    @enderror
  </div>

  <!-- Correo -->
  <div class="col-md-4 mb-3">
    <label for="correo" class="form-label">Correo</label>
    <input type="email" class="form-control @error('correo') is-invalid @enderror" id="correo" name="correo"
      value="{{ old('correo', $agro->correo ?? '') }}">
    @error('correo')
    <span class="invalid-feedback" role="alert">
      <strong>{{ $message }}</strong>
    </span>
    @enderror
  </div>

  <!-- Marcas -->
  <div class="col-md-4 mb-3">
    <label for="marcas" class="form-label">Marcas</label>
    <input type="text" class="form-control @error('marcas') is-invalid @enderror" id="marcas" name="marcas"
      value="{{ old('marcas', $agro->marcas ?? '') }}">
    @error('marcas')
    <span class="invalid-feedback" role="alert">
      <strong>{{ $message }}</strong>
    </span>
    @enderror
  </div>

  <!-- Rubro -->
  <div class="col-md-4 mb-3">
    <label for="rubro_id" class="form-label">Rubro</label>
    <select class="form-control @error('rubro_id') is-invalid @enderror" id="rubro_id" name="rubro_id">
      <option value="">Seleccione un rubro</option>
      @foreach($rubros as $rubro)
      <option value="{{ $rubro->id }}" {{ old('rubro_id', $agro->rubro_id ?? '') == $rubro->id ? 'selected' : ''
        }}>
        {{ sin_dato($rubro->nombre) }}
      </option>
      @endforeach
    </select>
    @error('rubro_id')
    <span class="invalid-feedback" role="alert">
      <strong>{{ $message }}</strong>
    </span>
    @enderror
  </div>

  <!-- Información -->
  <div class="col-md-12 mb-3">
    <label for="info" class="form-label">Información</label>
    <textarea class="form-control @error('info') is-invalid @enderror" id="info"
      name="info">{{ old('info', $agro->info ?? '') }}</textarea>
    @error('info')
    <span class="invalid-feedback" role="alert">
      <strong>{{ $message }}</strong>
    </span>
    @enderror
  </div>
</div>