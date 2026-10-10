@foreach (['success' => 'success', 'info' => 'info', 'warning' => 'warning', 'error' => 'danger'] as $clave => $clase)
@if (session($clave))
<div class="alert alert-{{ $clase }} alert-dismissible fade show mt-2" role="alert">
  {{ session($clave) }}
  <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
</div>
@endif
@endforeach

@if ($errors->any())
<div class="alert alert-danger alert-dismissible fade show mt-2" role="alert">
  <ul class="mb-0 pl-3">
    @foreach ($errors->all() as $error)
    <li>{{ $error }}</li>
    @endforeach
  </ul>
  <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
</div>
@endif
