{{-- Productos que compra el cliente, para cargarlos al pedido, y alta de los que no están en su lista --}}
<h4 class="mt-3">Productos que compra el cliente</h4>
<div class="table-responsive">
  <table class="table table-sm table-bordered sin-tarjetas" id="productosCliente">
    <thead>
      <tr>
        <th>Producto</th>
        <th class="text-right">Precio</th>
        <th class="text-center">Últ. cotización</th>
        <th class="text-center">Últ. entrega</th>
        <th style="min-width: 90px; width: 110px">Cantidad</th>
        <th class="text-center">Flag</th>
        <th class="text-center" title="Sin cargo">S/C</th>
        <th class="text-center">Retirar</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <tr><td colspan="9" class="text-center text-muted">Seleccione un cliente.</td></tr>
    </tbody>
  </table>
</div>

@can('permiso_11')
<div class="card card-body bg-light p-2 mb-3">
  <strong class="mb-1">¿No está en la lista? Agregar producto al cliente</strong>
  <div class="custom-control custom-checkbox mb-1">
    <input type="checkbox" class="custom-control-input" id="catalogo_crear">
    <label class="custom-control-label font-weight-normal" for="catalogo_crear">No está en el catálogo: crear producto nuevo</label>
  </div>
  <div class="form-row align-items-end">
    <div class="col-md-5 mb-2" id="catalogo_existente">
      <label for="catalogo_id" class="mb-0">Producto</label>
      <select id="catalogo_id" class="form-control" data-buscador>
        <option value="">Seleccione un producto</option>
        @foreach ($catalogo as $p)
        <option value="{{ $p->id }}">{{ $p->nombre() }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-8 col-md-3 mb-2 d-none" data-catalogo-nuevo>
      <label for="catalogo_nombre" class="mb-0">Producto nuevo</label>
      <input type="text" maxlength="150" id="catalogo_nombre" class="form-control">
    </div>
    <div class="col-4 col-md-2 mb-2 d-none" data-catalogo-nuevo>
      <label for="catalogo_iva" class="mb-0">IVA</label>
      <select id="catalogo_iva" class="form-control">
        <option value="">Seleccione</option>
        <option value="10.5">10,5 %</option>
        <option value="21">21 %</option>
      </select>
    </div>
    <div class="col-6 col-md-2 mb-2">
      <label for="catalogo_precio" class="mb-0">Precio</label>
      <input type="number" step="0.01" min="0" id="catalogo_precio" class="form-control">
    </div>
    <div class="col-6 col-md-3 mb-2">
      <label for="catalogo_fecha" class="mb-0">Fecha cotización</label>
      <input type="date" id="catalogo_fecha" class="form-control" value="{{ now()->toDateString() }}">
    </div>
    <div class="col-md-2 mb-2">
      <button type="button" id="catalogo_agregar" class="btn btn-primary btn-block">Agregar a la lista</button>
    </div>
  </div>
  <div id="catalogo_error" class="text-danger small" role="alert"></div>
</div>
@endcan

<div class="form-row align-items-end mb-3" id="tareas">
  <div class="col-md-5">
    <label for="tarea_id" class="mb-0">Tarea</label>
    <select id="tarea_id" class="form-control">
      <option value="">Seleccione</option>
    </select>
  </div>
  <div class="col-md-5">
    <label for="tarea_detalle" class="mb-0">Detalle</label>
    <input type="text" id="tarea_detalle" class="form-control">
  </div>
  <div class="col-md-2">
    <button type="button" id="tarea_agregar" class="btn btn-success mt-2">Agregar tarea</button>
  </div>
</div>
