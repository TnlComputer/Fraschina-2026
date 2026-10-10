@extends('adminlte::page')

@section('title', 'Editar Pedido')

@section('content_header')
<h1>Editar Pedido #{{ $pedido->id }}</h1>
@endsection

@section('content')
<div class="card">
  <div class="card-body">
    <form action="{{ route('distribucion_pedido.update', $pedido->id) }}" method="POST" id="pedidoForm">
      @csrf
      @method('PUT')

      <!-- Información General -->
      <div class="row mb-3">
        <div class="col-md-4">
          <label>Cliente:</label>
          <input type="text" class="form-control" value="{{ $pedido->distribucion->nomfantasia }}" disabled>
        </div>
        <div class="col-md-4">
          <label>Fecha de Entrega:</label>
          <input type="date" name="fecha_entrega" class="form-control" value="{{ $pedido->fechaEntrega }}" required>
        </div>
        <div class="col-md-4">
          <label>Observaciones:</label>
          <input type="text" name="observaciones" class="form-control" value="{{ $pedido->observaciones }}">
        </div>
      </div>

      <!-- Detalles del Pedido -->
      <h4>Detalles del Pedido</h4>
      <table class="table table-bordered" id="detalleTable">
        <thead>
          <tr>
            <th>Tipo</th>
            <th>Descripción</th>
            <th>Precio</th>
            <th>Cantidad</th>
            <th>Total</th>
            <th>Acción</th>
          </tr>
        </thead>
        <tbody>
          {{-- Productos existentes --}}
          @foreach($pedido->lineasPedidos as $linea)
          <tr data-linea-id="{{ $linea->id }}" data-iva="{{ $linea->producto?->porcentajeIva() ?? 21 }}" class="producto-row">
            <td>Producto</td>
            <td>
              {{ $linea->producto?->productoCDA }}
              <input type="hidden" name="existing_product_line_ids[]" value="{{ $linea->id }}">
            </td>
            <td>
              <input type="number" step="0.01" name="existing_precios[]" value="{{ $linea->precio_unitario }}"
                class="form-control" required>
            </td>
            <td>
              <input type="number" name="existing_cantidades[]" value="{{ $linea->cantidad }}" class="form-control"
                required>
            </td>
            <td class="linea-total">{{ pesos($linea->totalLinea) }}</td>
            <td>
              <button type="button" class="btn btn-danger remove-row">X</button>
            </td>
          </tr>
          @endforeach

          {{-- Tareas existentes --}}
          @foreach($pedido->lineasTareas as $linea)
          <tr data-tarea-id="{{ $linea->id }}" class="tarea-row">
            <td>Tarea</td>
            <td>
              {{ $linea->tarea->tarea }}
              <input type="hidden" name="existing_tarea_ids[]" value="{{ $linea->id }}">
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td>
              <button type="button" class="btn btn-danger remove-row">X</button>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>

      <!-- Sección para agregar nuevos productos o tareas -->
      <h4>Agregar Producto o Tarea</h4>
      <div class="row mb-3">
        <div class="col-md-2">
          <label for="tipo">Tipo:</label>
          <select id="tipo" class="form-control">
            <option value="">Seleccione</option>
            <option value="P">Producto</option>
            <option value="T">Tarea</option>
          </select>
        </div>
        <div class="col-md-3">
          <label for="item_id">Producto/Tarea:</label>
          <select id="item_id" class="form-control">
            <option value="">Seleccione</option>
          </select>
        </div>
        <div class="col-md-2" id="precioContainer" style="display: none;">
          <label for="precio">Precio:</label>
          <input type="number" step="0.01" id="precio" class="form-control">
        </div>
        <div class="col-md-2" id="cantidadContainer" style="display: none;">
          <label for="cantidad">Cantidad:</label>
          <input type="number" id="cantidad" class="form-control">
        </div>
        <div class="col-md-3 d-flex align-items-end">
          <button type="button" id="addItemBtn" class="btn btn-success">Agregar</button>
        </div>
      </div>

      <!-- Los nuevos ítems se agregarán dinámicamente a la tabla -->
      <button type="submit" class="btn btn-primary">Guardar Cambios</button>
      <a href="{{ route('distribucion_reparto.index', ['fecha' => $pedido->fechaEntrega]) }}"
        class="btn btn-secondary">Cancelar</a>
    </form>
  </div>
</div>

<!-- Script para manejar la carga dinámica -->
<script>
  document.addEventListener('DOMContentLoaded', function() {
  // Al cambiar el tipo, cargar la lista de productos o tareas
  document.getElementById('tipo').addEventListener('change', function() {
    let tipo = this.value;
    let select = document.getElementById('item_id');
    select.innerHTML = '<option value="">Seleccione</option>';
    // Mostrar u ocultar campos de precio y cantidad
    if (tipo === 'P') {
      document.getElementById('precioContainer').style.display = 'block';
      document.getElementById('cantidadContainer').style.display = 'block';
    } else {
      document.getElementById('precioContainer').style.display = 'none';
      document.getElementById('cantidadContainer').style.display = 'none';
    }
    // Solicitar productos/tareas desde el servidor (ajusta la URL según tu ruta)
    let distribucionId = "{{ $pedido->distribucion_id }}";
    fetch(`/distribucion/${distribucionId}/productos-tareas`)
      .then(response => response.json())
      .then(data => {
        let options = '<option value="">Seleccione</option>';
        if (tipo === 'P') {
          data.productos.forEach(item => {
            options += `<option value="${item.producto_id}" data-precio="${item.precio}" data-iva="${ivaDe(item.ivacda)}">${item.nombre}</option>`;
          });
        } else if (tipo === 'T') {
          data.tareas.forEach(item => {
            options += `<option value="${item.id}">${item.tarea}</option>`;
          });
        }
        select.innerHTML = options;
      })
      .catch(err => console.error(err));
  });

  // Al seleccionar un producto, auto-asigna el precio
  document.getElementById('item_id').addEventListener('change', function() {
    let selected = this.options[this.selectedIndex];
    let precio = selected.getAttribute('data-precio');
    if (precio) {
      document.getElementById('precio').value = precio;
    }
  });

  // Agregar un nuevo ítem a la tabla
  document.getElementById('addItemBtn').addEventListener('click', function() {
    let tipo = document.getElementById('tipo').value;
    let itemSelect = document.getElementById('item_id');
    let itemId = itemSelect.value;
    let itemText = itemSelect.options[itemSelect.selectedIndex].text;
    if (!tipo || !itemId) {
      alert("Seleccione tipo y producto/tarea.");
      return;
    }
    let tableBody = document.querySelector('#detalleTable tbody');
    let newRow = document.createElement('tr');
    if (tipo === 'P') {
      let precio = document.getElementById('precio').value;
      let cantidad = document.getElementById('cantidad').value;
      if (!precio || !cantidad) {
        alert("Ingrese precio y cantidad para el producto.");
        return;
      }
      let iva = parseFloat(itemSelect.options[itemSelect.selectedIndex].dataset.iva) || 21;
      let total = calcularTotalLinea(precio, cantidad, iva);
      newRow.dataset.iva = iva;
      newRow.innerHTML = `
        <td>Producto</td>
        <td>${itemText}
          <input type="hidden" name="new_productos[]" value="${itemId}">
        </td>
        <td>
          <input type="number" step="0.01" name="new_precios[]" value="${precio}" class="form-control" required>
        </td>
        <td>
          <input type="number" name="new_cantidades[]" value="${cantidad}" class="form-control" required>
        </td>
        <td class="linea-total">${pesos(total)}</td>
        <td><button type="button" class="btn btn-danger remove-row">X</button></td>
      `;
    } else if (tipo === 'T') {
      newRow.innerHTML = `
        <td>Tarea</td>
        <td>${itemText}
          <input type="hidden" name="new_tareas[]" value="${itemId}">
        </td>
        <td></td>
        <td></td>
        <td></td>
        <td><button type="button" class="btn btn-danger remove-row">X</button></td>
      `;
    }
    tableBody.appendChild(newRow);
  });

  // Delegación de evento para remover filas (ya sean existentes o nuevas)
  document.addEventListener('click', function(e) {
    if (e.target && e.target.classList.contains('remove-row')) {
      e.target.closest('tr').remove();
    }
  });
});

// Misma regla que productoCDA::porcentajeIva() y calcularTotalLinea() en el servidor
function ivaDe(ivacda) {
  return parseFloat(String(ivacda ?? '').replace(',', '.')) === 10.5 ? 10.5 : 21;
}

// Importe con formato argentino, igual que pesos() en el servidor: $ 1.234,56
function pesos(n) {
  return '$ ' + (Number(n) || 0).toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function calcularTotalLinea(precio, cantidad, iva) {
  return Math.round((parseFloat(precio) || 0) * (parseFloat(cantidad) || 0) * (1 + iva / 100) * 100) / 100;
}

document.addEventListener('DOMContentLoaded', function() {

// Actualiza el total (con IVA) de una fila, existente o nueva
function actualizarTotalFila(row) {
let precioInput = row.querySelector('input[name="existing_precios[]"], input[name="new_precios[]"]');
let cantidadInput = row.querySelector('input[name="existing_cantidades[]"], input[name="new_cantidades[]"]');
let totalCell = row.querySelector('.linea-total');
if (!precioInput || !cantidadInput || !totalCell) return;

let iva = parseFloat(row.dataset.iva) || 21;
let total = calcularTotalLinea(precioInput.value, cantidadInput.value, iva);

totalCell.textContent = pesos(total);
}

// Escuchar cambios en los inputs de precios y cantidades
document.querySelectorAll('input[name="existing_precios[]"], input[name="existing_cantidades[]"]').forEach(input => {
input.addEventListener('input', function() {
let row = this.closest('tr');
actualizarTotalFila(row);
});
});

// Delegación de eventos para nuevos productos agregados dinámicamente
document.getElementById('detalleTable').addEventListener('input', function(event) {
if (event.target.matches('input[name="new_precios[]"], input[name="new_cantidades[]"]')) {
let row = event.target.closest('tr');
actualizarTotalFila(row);
}
});

});

</script>
@endsection