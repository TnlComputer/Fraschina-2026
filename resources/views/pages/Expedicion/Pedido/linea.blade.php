{{-- Una línea del pedido. Recibe $i (índice o __I__ para la plantilla) y $l (valores). --}}
@php $n = "lineas[$i]"; @endphp
<div class="card card-outline card-secondary linea-pedido">
  <div class="card-header py-1 d-flex align-items-center">
    <strong>Línea <span class="numero-linea"></span></strong>
    <button type="button" class="btn btn-xs btn-outline-danger ml-auto quitar-linea" title="Quitar línea"><i class="fas fa-times"></i></button>
  </div>
  <div class="card-body py-2">
    <div class="form-row">
      <div class="col-md-3">
        <label class="small mb-0">Calidad *</label>
        <input type="text" name="{{ $n }}[calidad]" value="{{ $l['calidad'] ?? '' }}" class="form-control form-control-sm" maxlength="100" required>
      </div>
      <div class="col-md-1">
        <label class="small mb-0">Tolvas</label>
        <input type="number" min="0" name="{{ $n }}[tolvas]" value="{{ $l['tolvas'] ?? '' }}" class="form-control form-control-sm">
      </div>
      <div class="col-md-1">
        <label class="small mb-0">Bolsas</label>
        <input type="number" min="0" name="{{ $n }}[bolsas]" value="{{ $l['bolsas'] ?? '' }}" class="form-control form-control-sm">
      </div>
      <div class="col-md-1">
        <label class="small mb-0">Kilos</label>
        <input type="number" min="0" name="{{ $n }}[kilos]" value="{{ $l['kilos'] ?? '' }}" class="form-control form-control-sm">
      </div>
      <div class="col-md-2">
        <label class="small mb-0">Precio (+ IVA)</label>
        <input type="number" min="0" step="0.001" name="{{ $n }}[precio]" value="{{ $l['precio'] ?? '' }}" class="form-control form-control-sm">
      </div>
      <div class="col-md-2">
        <label class="small mb-0">Condición</label>
        <input type="text" name="{{ $n }}[condicion]" data-campo="condicion" value="{{ $l['condicion'] ?? '' }}" class="form-control form-control-sm" maxlength="100">
      </div>
      <div class="col-md-2">
        <label class="small mb-0">Planta</label>
        <input type="text" name="{{ $n }}[planta]" data-campo="planta" value="{{ $l['planta'] ?? '' }}" class="form-control form-control-sm" maxlength="100">
      </div>
    </div>
    <div class="form-row mt-1">
      <div class="col-md-2">
        <label class="small mb-0">Fecha de entrega</label>
        <input type="date" name="{{ $n }}[fecha_entrega]" value="{{ $l['fecha_entrega'] ?? '' }}" class="form-control form-control-sm">
      </div>
      <div class="col-md-1 d-flex align-items-end">
        <div class="form-check mb-1">
          <input type="hidden" name="{{ $n }}[fecha_tentativa]" value="0">
          <input type="checkbox" name="{{ $n }}[fecha_tentativa]" value="1" class="form-check-input" id="tent-{{ $i }}" @checked(!empty($l['fecha_tentativa']))>
          <label class="form-check-label small text-primary" for="tent-{{ $i }}">Tentativa</label>
        </div>
      </div>
      <div class="col-md-2">
        <label class="small mb-0">Nota de la fecha</label>
        <input type="text" name="{{ $n }}[nota_fecha]" value="{{ $l['nota_fecha'] ?? '' }}" class="form-control form-control-sm" maxlength="255">
      </div>
      <div class="col-md-2">
        <label class="small mb-0">Fecha alternativa</label>
        <input type="date" name="{{ $n }}[fecha_alternativa]" value="{{ $l['fecha_alternativa'] ?? '' }}" class="form-control form-control-sm">
      </div>
      <div class="col-md-1">
        <label class="small mb-0">Hora</label>
        <input type="text" name="{{ $n }}[hora]" value="{{ $l['hora'] ?? '' }}" class="form-control form-control-sm" maxlength="20" placeholder="08:00">
      </div>
      <div class="col-md-1">
        <label class="small mb-0">Plazo (días)</label>
        <input type="number" min="0" name="{{ $n }}[plazo_dias]" data-campo="plazo_dias" value="{{ $l['plazo_dias'] ?? '' }}" class="form-control form-control-sm">
      </div>
      <div class="col-md-3">
        <label class="small mb-0">Forma de pago</label>
        <input type="text" name="{{ $n }}[forma_pago]" data-campo="forma_pago" value="{{ $l['forma_pago'] ?? '' }}" class="form-control form-control-sm" maxlength="100">
      </div>
    </div>
    <div class="form-row mt-1">
      <div class="col-md-4">
        <label class="small mb-0">Lugar de entrega</label>
        <input type="text" name="{{ $n }}[lugar_entrega]" value="{{ $l['lugar_entrega'] ?? '' }}" class="form-control form-control-sm" maxlength="255">
      </div>
      <div class="col-md-8">
        <label class="small mb-0">Observaciones</label>
        <input type="text" name="{{ $n }}[observaciones]" value="{{ $l['observaciones'] ?? '' }}" class="form-control form-control-sm" maxlength="1000">
      </div>
    </div>
  </div>
</div>
