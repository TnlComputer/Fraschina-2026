<div class="card"><div class="card-body text-sm">
  <form id="distribucionForm" action="{{ $action }}" method="POST">
    @csrf
    @if ($method !== 'POST') @method($method) @endif
    <h5 class="form-seccion"><i class="fas fa-store"></i> Datos del cliente</h5>
    <div class="row g-3">
      <!-- Razón Social y Nombre Fantasía -->
      <div class="col-md-2">
        <label for="clisg_id" class="form-label">Nro. Cliente SG</label>
        <input type="text" name="clisg_id" id="clisg_id" class="form-control @error('clisg_id') is-invalid @enderror"
          value="{{ old('clisg_id', $distribucion->clisg_id ?? '') }}">
        @error('clisg_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div class="col-md-5">
        <label for="razonsocial" class="form-label">Razón Social</label>
        <input type="text" name="razonsocial" id="razonsocial"
          class="form-control @error('razonsocial') is-invalid @enderror"
          value="{{ old('razonsocial', $distribucion->razonsocial ?? '') }}" required>
        @error('razonsocial')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div class="col-md-5">
        <label for="nomfantasia" class="form-label">Nombre Fantasía</label>
        <input type="text" name="nomfantasia" id="nomfantasia"
          class="form-control @error('nomfantasia') is-invalid @enderror"
          value="{{ old('nomfantasia', $distribucion->nomfantasia ?? '') }}">
        @error('nomfantasia')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
    </div>

    <h5 class="form-seccion"><i class="fas fa-location-dot"></i> Dirección</h5>
    <div class="row g-3">
      <!-- Dirección -->
      <div class="col-md-3">
        <label for="dire_calle_id" class="form-label">Calle</label>
        <select name="dire_calle_id" id="dire_calle_id"
          class="form-control @error('dire_calle_id') is-invalid @enderror">
          <option value="">Seleccione una calle</option>
          @foreach ($calles as $calle)
          <option value="{{ $calle->id }}" {{ old('dire_calle_id', $distribucion->dire_calle_id ?? '') == $calle->id ?
            'selected' : '' }}>
            {{ $calle->calle }}
          </option>
          @endforeach
        </select>
        @error('dire_calle_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div class="col-md-1">
        <label for="dire_nro" class="form-label">Número</label>
        <input type="text" name="dire_nro" id="dire_nro" class="form-control @error('dire_nro') is-invalid @enderror"
          value="{{ old('dire_nro', $distribucion->dire_nro ?? '') }}">
        @error('dire_nro')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div class="col-md-1">
        <label for="piso" class="form-label">Piso</label>
        <input type="text" name="piso" id="piso" class="form-control @error('piso') is-invalid @enderror"
          value="{{ old('piso', $distribucion->piso ?? '') }}">
        @error('piso')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div class="col-md-1">
        <label for="dpto" class="form-label">Dpto</label>
        <input type="text" name="dpto" id="dpto" class="form-control @error('dpto') is-invalid @enderror"
          value="{{ old('dpto', $distribucion->dpto ?? '') }}">
        @error('dpto')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>

      <div class="col-md-4">
        <label for="dire_obs" class="form-label">Dirección Observaciones</label>
        <input type="text" name="dire_obs" id="dire_obs" class="form-control @error('dire_obs') is-invalid @enderror"
          value="{{ old('dire_obs', $distribucion->dire_obs ?? '') }}">
        @error('dire_obs')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>

      <div class="col-md-2">
        <label for="codpost" class="form-label">Código Postal</label>
        <input type="text" name="codpost" id="codpost" class="form-control @error('codpost') is-invalid @enderror"
          value="{{ old('codpost', $distribucion->codpost ?? '') }}">
        @error('codpost')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
    </div>
    <div class="row g-3">
      <!-- Campos aux* -->
      <div class="col-md-3">
        <label for="barrio_id" class="form-label">Barrio</label>
        <select name="barrio_id" id="barrio_id" class="form-control @error('auxbarrio') is-invalid @enderror">
          <option value="">Seleccione un Barrio</option>
          @foreach ($barrios as $barrio)
          <option value="{{ $barrio->id }}" {{ old('barrio_id', $distribucion->barrio_id ?? '') == $barrio->id ?
            'selected' : '' }}>
            {{ $barrio->nombrebarrio }}
          </option>
          @endforeach
        </select>
        @error('barrio_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>

      <div class="col-md-3">
        <label for="municipio_id" class="form-label">Municipio</label>
        <select name="municipio_id" id="municipio_id" class="form-control @error('municipio_id') is-invalid @enderror">
          <option value="">Seleccione un Municipio</option>
          @foreach ($municipios as $municipio)
          <option value="{{ $municipio->id }}" {{ old('municipio_id', $distribucion->municipio_id ?? '') ==
            $municipio->id ? 'selected' : '' }}>
            {{ $municipio->ciudadmunicipio }}
          </option>
          @endforeach
        </select>
        @error('municipio_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>

      <div class="col-md-3">
        <label for="localidad_id" class="form-label">Localidad</label>
        <select name="localidad_id" id="localidad_id" class="form-control @error('localidad_id') is-invalid @enderror">
          <option value="">Seleccione una Localidad</option>
          @foreach ($localidades as $localidad)
          <option value="{{ $localidad->id }}" {{ old('localidad_id', $distribucion->localidad_id ?? '') ==
            $localidad->id ? 'selected' : '' }}>
            {{ $localidad->localidad }}
          </option>
          @endforeach
        </select>
        @error('localidad_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>

      <div class="col-md-3">
        <label for="zona_id" class="form-label">Zona</label>
        <select name="zona_id" id="zona_id" class="form-control @error('zona_id') is-invalid @enderror">
          <option value="">Seleccione una Zona</option>
          @foreach ($zonas as $zona)
          <option value="{{ $zona->id }}" {{ old('zona_id', $distribucion->zona_id ?? '') == $zona->id ? 'selected' : ''
            }}>
            {{ $zona->nombre }}
          </option>
          @endforeach
        </select>
        @error('zona_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
    </div>

    <h5 class="form-seccion"><i class="fas fa-phone"></i> Contacto</h5>
    <div class="row g-3">
      <!-- Teléfono, Fax, CUIT y Correo -->
      <div class="col-md-3">
        <label for="telefono" class="form-label">Teléfono</label>
        <input type="text" name="telefono" id="telefono" class="form-control @error('telefono') is-invalid @enderror"
          value="{{ old('telefono', $distribucion->telefono ?? '') }}">
        @error('telefono')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      {{-- <div class="col-md-3">
        <label for="fax" class="form-label">Fax</label>
        <input type="text" name="fax" id="fax" class="form-control @error('fax') is-invalid @enderror"
          value="{{ old('fax', $distribucion->fax ?? '') }}">
        @error('fax')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div> --}}
      <div class="col-md-3">
        <label for="cuit" class="form-label">CUIT</label>
        <input type="text" name="cuit" id="cuit" class="form-control @error('cuit') is-invalid @enderror"
          value="{{ old('cuit', $distribucion->cuit ?? '') }}">
        @error('cuit')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div class="col-md-3">
        <label for="correo" class="form-label">Correo</label>
        <input type="email" name="correo" id="correo" class="form-control @error('correo') is-invalid @enderror"
          value="{{ old('correo', $distribucion->correo ?? '') }}">
        @error('correo')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
    </div>

    <h5 class="form-seccion"><i class="fas fa-briefcase"></i> Perfil comercial</h5>
    <div class="row g-3">
      <!-- Rubro, Tamaño y Modo -->
      <div class="col-md-3">
        <label for="rubro_id" class="form-label">Rubro</label>
        <select name="rubro_id" id="rubro_id" class="form-control @error('rubro_id') is-invalid @enderror">
          <option value="">Seleccione un Rubro</option>
          @foreach ($rubros as $rubro)
          <option value="{{ $rubro->id }}" {{ old('rubro_id', $distribucion->rubro_id ?? '') == $rubro->id ?
            'selected'
            : '' }}>
            {{ $rubro->nombre }}
          </option>
          @endforeach
        </select>
        @error('rubro_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>

      <div class="col-md-3">
        <label for="tamano" class="form-label">Tamaño</label>
        <select name="tamanio_id" id="tamano" class="form-control @error('tamanio_id') is-invalid @enderror">
          <option value="">Seleccione un Tamaño</option>
          @foreach ($tamanos as $tamanio)
          <option value="{{ $tamanio->id }}" {{ old('tamanio_id', $distribucion->tamanio_id ?? '') == $tamanio->id ?
            'selected' : '' }}>
            {{ $tamanio->nombre }}
          </option>
          @endforeach
        </select>
        @error('tamanio_id')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3">
        <label for="modo_id" class="form-label">Modo</label>
        <select name="modo_id" id="modo_id" class="form-control @error('modo_id') is-invalid @enderror">
          <option value="">Seleccione un Modo</option>
          @foreach ($modos as $modo)
          <option value="{{ $modo->id }}" {{ old('modo_id', $distribucion->modo_id ?? '') == $modo->id ? 'selected' :
            ''
            }}>
            {{ $modo->nombre }}
          </option>
          @endforeach
        </select>
        @error('modo_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <!-- Marcas -->
      <div class="col-md-3">
        <label for="marcas" class="form-label">Marcas</label>
        <input type="text" name="marcas" id="marcas" class="form-control @error('marcas') is-invalid @enderror"
          value="{{ old('marcas', $distribucion->marcas ?? '') }}">
        @error('marcas')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
    </div>

    <h5 class="form-seccion"><i class="fas fa-circle-info"></i> Situación</h5>
    <div class="row g-3">
      <!-- Contacto -->
      <div class="col-md-3">
        <label for="contacto_id" class="form-label">Contacto Inicial</label>
        <select name="contacto_id" id="contacto_id" class="form-control @error('contacto_id') is-invalid @enderror">
          <option value="">Seleccione un Contacto</option>
          @foreach ($contactos as $contacto)
          <option value="{{ $contacto->id }}" {{ old('contacto_id', $distribucion->contacto_id ?? '') == $contacto->id
            ?
            'selected' : '' }}>
            {{ $contacto->contacto }}
          </option>
          @endforeach
        </select>
        @error('contacto_id')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>
      {{--
    </div> --}}

    <div class="col-md-1">
      <label for="auto" class="form-label">Automático</label>
      <div class="form-check">
        <input type="checkbox" name="auto" id="auto" class="form-check-input" {{ \App\Models\Distribucion::esSi(old('auto', $distribucion->auto ?? null)) ? 'checked' : '' }}>
        <label class="form-check-label" for="auto">Sí</label>
      </div>
    </div>

    <!-- Veraz -->
    <div class="col-md-2">
      <label for="veraz_id" class="form-label">Veraz</label>
      <select name="veraz_id" id="veraz_id" class="form-control">
        @foreach ($verazs as $veraz)
        <option value="{{ $veraz->id }}" {{ old('veraz_id', $distribucion->veraz_id ?? '') == $veraz->id ? 'selected'
          :
          ''
          }}>
          {{ $veraz->estado }}
        </option>
        @endforeach
      </select>
    </div>
    <!-- Estado -->
    <div class="col-md-2">
      <label for="estado_id" class="form-label">Estado</label>
      <select name="estado_id" id="estado_id" class="form-control">
        @foreach ($estados as $estado)
        <option value="{{ $estado->id }}" {{ old('estado_id', $distribucion->estado_id ?? '') == $estado->id ?
          'selected'
          :
          '' }}>
          {{ $estado->nomEstado }}
        </option>
        @endforeach
      </select>
    </div>

    <!-- Productos -->
    <div class="col-md-4">
      <label for="productoCDA" class="form-label">Productos</label>
      <input type="text" name="productoCDA" id="productoCDA"
        class="form-control @error('productoCDA') is-invalid @enderror"
        value="{{ old('productoCDA', $distribucion->productoCDA ?? '') }}">
      @error('productoCDA')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<h5 class="form-seccion"><i class="fas fa-truck"></i> Entregas</h5>
<div class="row g-3">
  <div class="col-md-4">
    <label for="desde">Mañana</label>
  </div>
  <div class="col-md-4">
    <label for="desde">Tarde</label>
  </div>
</div>
<div class="row g-3">
  <!-- Mañana Desde / Hasta -->
  <div class="col-md-2">
    <label for="Mañana_desde">Desde</label>
    <input type="time" id="Mañana_desde" name="desde" class="form-control"
      value="{{ old('desde', $distribucion->desde ? \Carbon\Carbon::parse($distribucion->desde)->format('H:i') : '') }}">
  </div>
  <div class="col-md-2">
    <label for="Mañana_hasta">Hasta</label>
    <input type="time" id="Mañana_hasta" name="hasta" class="form-control"
      value="{{ old('hasta', $distribucion->hasta ? \Carbon\Carbon::parse($distribucion->hasta)->format('H:i') : '') }}">
  </div>
  <div class="col-md-2">
    <label for="Tarde_desde">Desde</label>
    <input type="time" id="Tarde_desde" name="desde1" class="form-control"
      value="{{ old('desde1', $distribucion->desde1 ? \Carbon\Carbon::parse($distribucion->desde1)->format('H:i') : '') }}">
  </div>
  <div class="col-md-2">
    <label for="Tarde_hasta">Hasta</label>
    <input type="time" id="Tarde_hasta" name="hasta1" class="form-control"
      value="{{ old('hasta1', $distribucion->hasta1 ? \Carbon\Carbon::parse($distribucion->hasta1)->format('H:i') : '') }}">
  </div>

  <div class="col-md-1">
    <label for="lunes" class="form-label">Lunes <i class="fa-solid fa-lock"></i></label>
    <div class="form-check">
      <input type="checkbox" name="lunes" id="lunes" class="form-check-input @error('lunes') is-invalid @enderror" {{
        \App\Models\Distribucion::esSi(old('lunes', $distribucion->lunes ?? null)) ? 'checked' : '' }}>
      <label class="form-check-label" for="lunes">Sí</label>
      @error('lunes')
      <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <div class="col-md-1">
    <label for="sabado" class="form-label">Sábado <i class="fa-solid fa-check"></i></label>
    <div class="form-check">
      <input type="checkbox" name="sabado" id="sabado" class="form-check-input @error('sabado') is-invalid @enderror" {{
        \App\Models\Distribucion::esSi(old('sabado', $distribucion->sabado ?? null)) ? 'checked' : '' }}>
      <label class="form-check-label" for="sabado">Sí</label>
      @error('sabado')
      <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <div class="col-md-1">
    <label for="fac_imp" class="form-label">Factura <i class="fa-solid fa-print fa-sm"></i></label>
    <div class="form-check">
      <input type="checkbox" name="fac_imp" id="fac_imp" class="form-check-input @error('fac_imp') is-invalid @enderror"
        {{ \App\Models\Distribucion::esSi(old('fac_imp', $distribucion->fac_imp ?? null)) ? 'checked' : '' }}>
      <label class="form-check-label" for="fac_imp">Sí</label>
      @error('fac_imp')
      <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>
  </div>

</div>

<h5 class="form-seccion"><i class="fas fa-cash-register"></i> Recepción y cobranza</h5>
<div class="row g-3">
  <div class="col-md-3">
    <!-- Observaciones Recepción -->
    <label for="obsrecep" class="form-label">Dirección Recepción</label>
    <input type="text" name="obsrecep" id="obsrecep" class="form-control @error('obsrecep') is-invalid @enderror"
      value="{{ old('obsrecep', $distribucion->obsrecep ?? '') }}">
    @error('obsrecep')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <!-- Cobrar -->
  <div class=" col-md-3">
    <label for="cobrar_id" class="form-label">Cobrar</label>
    <select name="cobrar_id" id="cobrar_id" class="form-control">
      @foreach ($cobrars as $cobrar)
      <option value="{{ $cobrar->id }}" {{ old('cobrar_id', $distribucion->cobrar_id ?? '') == $cobrar->id ?
        'selected'
        :
        '' }}>
        {{ $cobrar->accion }}
      </option>
      @endforeach
    </select>
  </div>

  <!-- Cobro -->
  <div class="col-md-3">
    <label for="cobro_id" class="form-label">Cobro</label>
    <select name="cobro_id" id="cobro_id" class="form-control">
      @foreach ($pagos as $cobro)
      <option value="{{ $cobro->id }}" {{ old('cobro_id', $distribucion->cobro_id ?? '') == $cobro->id ?
        'selected'
        : '' }}>
        {{ $cobro->nombre }}
      </option>
      @endforeach
    </select>
  </div>

  <!-- Tipo de Pago -->
  <div class="col-md-3">
    <label for="tcobro_id" class="form-label">Tipo de Pago</label>
    <select name="tcobro_id" id="tcobro_id" class="form-control">
      @foreach ($tiposPago as $tpago)
      <option value="{{ $tpago->id }}" {{ old('tcobro_id', $distribucion->tcobro_id ?? '') == $tpago->id ?
        'selected' :
        ''
        }}>
        {{ $tpago->nombre }}
      </option>
      @endforeach
    </select>
  </div>
</div>

<div class="mt-3">
  <h5 class="form-seccion"><i class="fas fa-note-sticky"></i> Información adicional</h5>
      <label for="info" class="form-label sr-only">Información Adicional</label>
  <textarea name="info" id="info" rows="2"
    class="form-control @error('info') is-invalid @enderror">{{ old('info', $distribucion->info ?? '') }}</textarea>
  @error('info')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div>
  @if ($errors->any())
  <div class="alert alert-danger">
    <ul>
      @foreach ($errors->all() as $error)
      <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
  @endif
</div>

<!-- Botones en la misma línea -->
<div class="row mt-4">
  <div class="col-12 form-acciones mt-3">
    <button type="submit" class="btn btn-primary">{{ $buttonText }}</button>
    <a href="{{ route('distribucion.index') }}" class="btn btn-outline-secondary">
      Cancelar
    </a>
  </div>
</div>
</form>
</div></div>