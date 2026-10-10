@extends('adminlte::page')

@section('title', 'Crear Pedido')

@section('content_header')
<h1>Nuevo Pedido</h1>
@endsection

@section('content')
<form id="pedido-form" action="{{ route('distribucion_pedido.store') }}" method="POST">
  @csrf

  <!-- Cliente -->
  <div class="form-group">
    <label for="cliente_id">Cliente:</label>
    <select name="cliente_id" id="cliente_id" class="form-control" required>
      <option value="">Seleccione un cliente</option>
      @foreach($clientes as $cliente)
      <option value="{{ $cliente->id }}">{{ unir($cliente->nomfantasia, $cliente->razonsocial) }}</option>
      @endforeach
    </select>
  </div>

  <!-- Fechas -->
  <div class="row">
    <div class="col-md-6">
      <label for="fecha_pedido">Fecha del Pedido:</label>
      <input type="date" id="fecha_pedido" class="form-control" value="{{ date('Y-m-d') }}" readonly>
    </div>
    <div class="col-md-6">
      <label for="fecha_entrega">Fecha de Entrega:</label>
      <input type="date" name="fecha_entrega" id="fecha_entrega" class="form-control" required>
    </div>
  </div>

  <!-- Agregar Producto/Tarea -->
  <h4 class="mt-3">Agregar Producto o Tarea</h4>
  <div class="row">
    <div class="col-md-2">
      <label for="tipo">Tipo:</label>
      <select id="tipo" class="form-control">
        <option value="P">Producto</option>
        <option value="T">Tarea</option>
      </select>
    </div>
    <div class="col-md-3">
      <label for="producto_id">Producto/Tarea:</label>
      <select id="producto_id" class="form-control">
        <option value="">Seleccione</option>
      </select>
    </div>
    <div class="col-md-2">
      <label for="precio">Precio:</label>
      <input type="number" id="precio" class="form-control" placeholder="Precio">
    </div>
    <div class="col-md-2">
      <label for="cantidad">Cantidad:</label>
      <input type="number" id="cantidad" class="form-control" placeholder="Cantidad">
    </div>
    <div class="col-md-3 d-flex align-items-end">
      <button type="button" id="add-item" class="btn btn-success">Agregar</button>
    </div>
  </div>

  <!-- Tabla de Productos/Tareas Agregados -->
  <h4 class="mt-3">Detalles del Pedido</h4>
  <table class="table table-bordered">
    <thead>
      <tr>
        <th>Tipo</th>
        <th>Descripción</th>
        <th>Precio</th>
        <th>Cantidad</th>
        <th>Acción</th>
      </tr>
    </thead>
    <tbody id="pedido-detalle">
      <!-- Se cargarán aquí los productos/tareas agregados -->
    </tbody>
  </table>

  <!-- Campo de Observaciones -->
  <div class="form-group">
    <label for="observaciones">Observaciones:</label>
    <textarea name="observaciones" id="observaciones" class="form-control" rows="3"></textarea>
  </div>

  <button type="submit" class="btn btn-primary">Guardar Pedido</button>
</form>

<script>
  let productosDisponibles = [];
  let tareasDisponibles = [];

  document.getElementById('cliente_id').addEventListener('change', async function() {
    let clienteId = this.value;
    if (!clienteId) return;

    try {
      let response = await fetch(`/productos-y-tareas-por-cliente/${clienteId}`);
      let data = await response.json();
      
      productosDisponibles = data.productos;
      tareasDisponibles = data.tareas;

      console.log(tareasDisponibles);

      actualizarSelectProductos();
    } catch (error) {
      console.error('Error cargando productos/tareas:', error);
    }
  });

  document.getElementById('tipo').addEventListener('change', actualizarSelectProductos);

  function actualizarSelectProductos() {
  let tipo = document.getElementById('tipo').value;
  let select = document.getElementById('producto_id');
  let precioInput = document.getElementById('precio');
  let cantidadInput = document.getElementById('cantidad');
  
  select.innerHTML = '<option value="">Seleccione</option>';
  let lista = tipo === 'P' ? productosDisponibles : tareasDisponibles;
  
  console.log("Lista de elementos:", lista);
  
  lista.forEach(item => {
  let nombre = tipo === 'P' ? item.nombre : item.tarea; // Usar el campo correcto
  select.innerHTML += `<option value="${item.id}" data-precio="${item.precio || 0}">${nombre}</option>`;
  });
  
  // Ocultar cantidad y precio si es una tarea
  if (tipo === 'T') {
  precioInput.closest('.col-md-2').style.display = 'none';
  cantidadInput.closest('.col-md-2').style.display = 'none';
  } else {
  precioInput.closest('.col-md-2').style.display = 'block';
  cantidadInput.closest('.col-md-2').style.display = 'block';
  }
  }

  document.getElementById('producto_id').addEventListener('change', function() {
    let selectedOption = this.options[this.selectedIndex];
    let tipo = document.getElementById('tipo').value;
    if (tipo === 'P') {
      document.getElementById('precio').value = selectedOption.dataset.precio || '';
    }
  });

  document.getElementById('add-item').addEventListener('click', function () {
    let tipo = document.getElementById('tipo').value;
    let productoSelect = document.getElementById('producto_id');
    let productoId = productoSelect.value;
    let productoNombre = productoSelect.options[productoSelect.selectedIndex].text;
    let cantidad = document.getElementById('cantidad').value;
    let precio = document.getElementById('precio').value;

    if (!productoId) {
      alert("Seleccione un producto o tarea.");
      return;
    }

    if (tipo === 'P' && (!cantidad || !precio)) {
      alert("Especifique la cantidad y precio para el producto.");
      return;
    }

    let tableBody = document.getElementById('pedido-detalle');
    let newRow = document.createElement('tr');

    newRow.innerHTML = `
      <td>${tipo === 'P' ? 'Producto' : 'Tarea'}</td>
      <td>${productoNombre}
        <input type="hidden" name="productos[]" value="${productoId}">
        <input type="hidden" name="tipos[]" value="${tipo}">
      </td>
      <td>${tipo === 'P' ? precio : '-'} <input type="hidden" name="precios[]" value="${tipo === 'P' ? precio : ''}"></td>
      <td>${tipo === 'P' ? cantidad : '-'} <input type="hidden" name="cantidades[]" value="${tipo === 'P' ? cantidad : ''}"></td>
      <td><button type="button" class="btn btn-danger remove-item">X</button></td>
    `;

    tableBody.appendChild(newRow);

    // Limpiar inputs
    document.getElementById('cantidad').value = "";
    document.getElementById('precio').value = "";
  });
</script>
@endsection