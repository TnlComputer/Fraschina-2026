@extends('adminlte::page')

@section('content')
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
  .no-spin {
    -moz-appearance: textfield;
    appearance: none;
  }

  .no-spin::-webkit-inner-spin-button,
  .no-spin::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
  }
</style>
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card-header">
        <h3 class="card-title">Calidad (General)</h3>
      </div>
      {{-- <div class="py-2"> --}}
        <form action="{{ route('expedicion_general.index') }}" method="GET">
          <table class="table table-sm table-striped table-bordered w-100">
            <div class="barra__index d-flex justify-content-between align-items-center mb-3">
              <a href="{{ route('expedicion_general.create') }}" class="btn btn-success">
                Nuevo
              </a>
              <a href="{{ route('expedicion_general.export', request()->query()) }}" class="btn btn-info">
                Exportar a Excel
              </a>
              <div class="div__nuevo">
                <button type="submit" class="btn btn-secondary btn-sm">Buscar</button>
                {{-- <form action="{{ route('expedicion_general.create') }}">
                  <input class="btn btn-success" type="submit" value="Nuevo"> --}}
                </form>
              </div>
            </div>

            {{-- <div class="table-responsive"> --}}

              {{-- <table class="table table-sm table-striped table-bordered"> --}}
                <thead>
                  {{-- <tr>
                    <th colspan="2">

                    </th>
                    <th colspan="14"></th>
                    <th colspan="3">

                    </th>
                  </tr> --}}
                  <tr class="text-center">
                    <th></th>
                    <th>FECHA <input type="text" name="fecha" class="form-control text-sm text-center"
                        value="{{ request('fecha') }}" style="padding: 0; margin: 0;"> </th>

                    <th>MO <input type="text" name="mo" class="form-control text-xs text-center"
                        value="{{ request('mo') }}" style="padding: 0; margin: 0;"> </th>

                    <th>CLI <input type="text" name="cl" class="form-control text-xs text-center"
                        value="{{ request('cl') }}" style="padding: 0; margin: 0;"> </th>

                    <th>Modo <input type="text" name="modo" class="form-control text-xs text-center"
                        value="{{ request('modo') }}" style="padding: 0; margin: 0;"> </th>

                    <th>Prod <input type="text" name="prod" class="form-control text-xs text-center"
                        value="{{ request('prod') }}" style="padding: 0; margin: 0;"> </th>

                    <th>P <input type="text" name="p" class="form-control text-xs text-center"
                        value="{{ request('p') }}" style="padding: 0; margin: 0;"> </th>

                    <th>L <input type="text" name="l" class="form-control  text-xs text-center"
                        value="{{ request('l') }}" style="padding: 0; margin: 0;"> </th>

                    <th>PL <input type="text" name="pl" class="form-control  text-xs text-center"
                        value="{{ request('pl') }}" style="padding: 0; margin: 0;"> </th>

                    <th>W <input type="text" name="w" class="form-control  text-xs text-center"
                        value="{{ request('w') }}" style="padding: 0; margin: 0;"> </th>

                    <th>GH <input type="text" name="gh" class="form-control  text-xs text-center"
                        value="{{ request('gh') }}" style="padding: 0; margin: 0;"> </th>

                    <th>GS <input type="text" name="gs" class="form-control  text-xs text-center"
                        value="{{ request('gs') }}" style="padding: 0; margin: 0;"> </th>

                    <th>GI <input type="text" name="gi" class="form-control  text-xs text-center"
                        value="{{ request('gi') }}" style="padding: 0; margin: 0;"> </th>

                    <th>HUM <input type="text" name="hum" class="form-control text-xs text-center"
                        value="{{ request('hum') }}" style="padding: 0; margin: 0;"> </th>

                    <th>CZ <input type="text" name="cz" class="form-control  text-xs text-center"
                        value="{{ request('cz') }}" style="padding: 0; margin: 0;"> </th>

                    <th>EST <input type="text" name="est" class="form-control text-xs text-center"
                        value="{{ request('est') }}" style="padding: 0; margin: 0;"> </th>

                    <th>ABS <input type="text" name="abs" class="form-control text-xs text-center"
                        value="{{ request('abs') }}" style="padding: 0; margin: 0;"> </th>

                    <th>FN <input type="text" name="fn" class="form-control  text-xs text-center"
                        value="{{ request('fn') }}" style="padding: 0; margin: 0;"> </th>

                    <th>Punt <input type="text" name="punt" class="form-control text-xs text-center"
                        value="{{ request('punt') }}" style="padding: 0; margin: 0;"> </th>

                    <th class="text-left uppercase">Particularidades <input type="text" name="particularidades"
                        class="form-control text-sm" value="{{ request('particularidades') }}"
                        style="padding: 0; margin: 0;"> </th>
                  </tr>
                </thead>

                <tbody>
                  @forelse($expedicion_general as $expedicion_gral)
                  <tr class="text-sm text-center w-full">
                    <td></td>
                    <td>{{ fecha($expedicion_gral->fecha) }}</td>
                    <td>{{ $expedicion_gral->mo }}</td>
                    <td>{{ $expedicion_gral->cl }}</td>
                    <td>{{ $expedicion_gral->modo }}</td>
                    <td>{{ $expedicion_gral->prod }}</td>
                    <td>{{ $expedicion_gral->p }}</td>
                    <td>{{ $expedicion_gral->l }}</td>
                    <td>{{ $expedicion_gral->pl }}</td>
                    <td>{{ $expedicion_gral->w }}</td>
                    <td>{{ $expedicion_gral->gh }}</td>
                    <td>{{ $expedicion_gral->gs }}</td>
                    <td>{{ $expedicion_gral->gi }}</td>
                    <td>{{ $expedicion_gral->hum }}</td>
                    <td>{{ $expedicion_gral->cz }}</td>
                    <td>{{ $expedicion_gral->est }}</td>
                    <td>{{ $expedicion_gral->abs }}</td>
                    <td>{{ $expedicion_gral->fn }}</td>
                    <td>{{ $expedicion_gral->punt }}</td>
                    <td class="text-left" style="min-width: 100px; max-width: 200px; word-wrap: break-word;">
                      {{ $expedicion_gral->particularidades }}
                    </td>
                    {{-- <td class="text-left">{{ $expedicion_gral->particularidades }}</td> --}}
                    {{-- <td class="d-flex justify-content-center">
                      @can('permiso_12')
                      <a href="{{ route('expedicion_general.edit', $expedicion_gral->id) }}"
                        class="btn btn-warning btn-xs mx-1" title="Editar">
                        <i class="fa-solid fa-pen-to-square"></i>
                      </a>
                      @endcan
                      @can('permiso_13')
                      <form method="POST" action="{{ route('expedicion_general.destroy', $expedicion_gral->id) }}"
                        id="form-desactivar-{{ $expedicion_gral->id }}">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-danger btn-xs mx-1" title="Eliminar"
                          onclick="confirmDesactivar({{ $expedicion_gral->id }})">
                          <i class="fa-solid fa-trash"></i>
                        </button>
                      </form>
                      @endcan
                    </td> --}}

                  </tr>
                  @empty
                  <tr>
                    <td colspan="19" class="text-center">No hay registros para mostrar...</td>
                  </tr>
                  @endforelse
                </tbody>
                {{-- <tbody>
                  @forelse($expedicion_general as $expedicion_gral)
                  <tr class="text-sm text-center w-full">
                    <td>U</td>
                    <td>{{ fecha($expedicion_gral->fecha) }}</td>
                    <td>{{ $expedicion_gral->mo }}</td>
                    <td>{{ $expedicion_gral->cl }}</td>
                    <td>{{ $expedicion_gral->modo }}</td>
                    <td>{{ $expedicion_gral->prod }}</td>
                    <td>
                      <input type="number" name="p[]" class="form-control text-xs text-center"
                        value="{{ $expedicion_gral->p }}">
                    </td>
                    <td>
                      <input type="number" name="l[]" class="form-control text-xs text-center"
                        value="{{ $expedicion_gral->l }}">
                    </td>
                    <td>
                      <input type="number" name="pl[]" class="form-control text-xs text-center"
                        value="{{ $expedicion_gral->pl }}" step="0.01">
                    </td>
                    <td>
                      <input type="number" name="w[]" class="form-control text-xs text-center"
                        value="{{ $expedicion_gral->w }}">
                    </td>
                    <td>
                      <input type="number" name="gh[]" class="form-control text-xs text-center"
                        value="{{ $expedicion_gral->gh }}">
                    </td>
                    <td>
                      <input type="number" name="gs[]" class="form-control text-xs text-center"
                        value="{{ $expedicion_gral->gs }}">
                    </td>
                    <td>
                      <input type="number" name="gi[]" class="form-control text-xs text-center"
                        value="{{ $expedicion_gral->gi }}">
                    </td>
                    <td>
                      <input type="number" name="hum[]" class="form-control text-xs text-center"
                        value="{{ $expedicion_gral->hum }}" step="0.01">
                    </td>
                    <td>
                      <input type="number" name="cz[]" class="form-control text-xs text-center"
                        value="{{ $expedicion_gral->cz }}" step="0.001">
                    </td>
                    <td>
                      <input type="number" name="est[]" class="form-control text-xs text-center"
                        value="{{ $expedicion_gral->est }}">
                    </td>
                    <td>
                      <input type="number" name="abs[]" class="form-control text-xs text-center"
                        value="{{ $expedicion_gral->abs }}" step="0.01">
                    </td>
                    <td>
                      <input type="number" name="fn[]" class="form-control text-xs text-center"
                        value="{{ $expedicion_gral->fn }}">
                    </td>
                    <td>
                      <input type="number" name="punt[]" class="form-control text-xs text-center no-spin"
                        value="{{ $expedicion_gral->punt }}">
                    </td>
                    <td class="text-left" style="min-width: 100px; max-width: 200px; word-wrap: break-word;">
                      <input type="text" name="particularidades[]" class="form-control text-sm"
                        value="{{ $expedicion_gral->particularidades }}">
                    </td>
                  </tr>
                  @empty
                  <tr>
                    <td colspan="19" class="text-center">No hay registros para mostrar...</td>
                  </tr>
                  @endforelse
                </tbody> --}}

              </table>

              {{ $expedicion_general->appends(request()->query())->links() }}
            </div>
        </form>
        {{--
      </div> --}}
    </div>
  </div>
</div>

<script>
  function confirmDesactivar(id) {
      Swal.fire({
          title: '¿Estás seguro?',
          text: 'Este registro será eliminado, ¿deseas continuar?',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Sí, eliminar',
          cancelButtonText: 'Cancelar',
          reverseButtons: true
      }).then((result) => {
          if (result.isConfirmed) {
              document.getElementById('form-desactivar-' + id).submit();
          }
      });
  }
 
//  document.querySelector("form").addEventListener("submit", function(event) {
//     let fields = [
//         { name: "p", min: 20, max: 200 },
//         { name: "l", min: 10, max: 300 },
//         { name: "pl", min: 0.15, max: 7.00 },
//         { name: "w", min: 42, max: 600 },
//         { name: "gh", min: 0, max: 45 },
//         { name: "gs", min: 0, max: 20 },
//         { name: "gi", min: 0, max: 100 },
//         { name: "hum", min: 8.00, max: 17.00 },
//         { name: "cz", min: 0.250, max: 1.200 },
//         { name: "est", min: 0, max: 30 },
//         { name: "abs", min: 40.00, max: 70.00 },
//         { name: "fn", min: 62, max: 600 },
//         { name: "punt", min: 0, max: 100 },
//     ];

//     let valid = true;
//     fields.forEach(field => {
//         let input = document.querySelector(`input[name="${field.name}"]`);
//         if (input !== undefined) {
//             let value = parseFloat(input.value);
//             if (value < field.min || value > field.max) {
//                 valid = false;
//                 alert(`El valor de ${field.name.toUpperCase()} debe estar entre ${field.min} y ${field.max}`);
//             }
//         }
//     });

//     if (!valid) {
//         event.preventDefault();
//     }
// });
</script>
@endsection