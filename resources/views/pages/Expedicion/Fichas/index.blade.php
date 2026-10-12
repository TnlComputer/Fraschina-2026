@extends('adminlte::page')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card-header">
        <h3 class="card-title">{{ __('Fichas') }}</h3>
      </div>
      <div class="col-12">
        <div class="py-2">
          <div class="max-w-8xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
              <div class="p-2 text-gray-900 text-left text-sm">

                {{-- Selector de Cliente --}}
                <form method="GET" action="{{ route('expedicion_fichas.index') }}" class="mb-3">
                  <div class="d-flex align-items-center">
                    <label for="cliente" class="m-2">Seleccionar Cliente </label>
                    <select name="origen_tabla" id="cliente" class="form-control m-2" style="max-width: 300px;">
                      <option value="">-- Seleccione un Cliente --</option>
                      @foreach ($clientes as $cliente)
                      <option value="{{ $cliente->origen_tabla }}" {{ request('origen_tabla')==$cliente->origen_tabla ?
                        'selected' : '' }}>
                        {{ $cliente->origen_tabla }}
                      </option>
                      @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                  </div>
                </form>

                {{-- Solo mostrar la tabla si se ha seleccionado un cliente --}}
                @if(request('origen_tabla'))
                <table class="table table-sm table-striped table-bordered w-100">
                  <tr>
                    <th><strong>M</strong></th>
                    <th><strong>PM</strong></th>
                    <th><strong>*</strong></th>
                    <th><strong>FE</strong></th>
                    <th><strong>OR</strong></th>
                    <th><strong>PL</strong></th>
                    <th><strong>CA</strong></th>
                    <th><strong>TB</strong></th>
                    <th><strong>K</strong></th>
                    <th><strong>P</strong></th>
                    <th><strong>I</strong></th>
                    <th><strong>D</strong></th>
                    <th><strong>N</strong></th>
                    <th><strong>FP</strong></th>
                    <th><strong>PP</strong></th>
                    <th><strong>O</strong></th>
                    <th><strong>Ficha</strong></th>
                  </tr>
                  @foreach ($fichas as $ficha)
                  @php
                  $orig = $ficha->extra ?? [];
                  // valor tal como estaba en la planilla cuando no se pudo convertir (ej. fecha mal tipeada)
                  $ver = fn($valor, $col) => $valor !== null && $valor !== '' ? e($valor)
                    : (isset($orig[$col]) ? '<span class="text-danger" title="Valor original de la planilla">' . e($orig[$col]) . '</span>' : '');
                  $otras = collect($orig)->filter(fn($v, $k) => str_starts_with($k, 'col_'))->implode(' · ');
                  @endphp
                  <tr class="text-sm">
                    <td align="right">{!! $ver($ficha->m, 'M') !!}</td>
                    <td>{!! $ver($ficha->pn, 'PN') !!}</td>
                    <td>{{ $ficha->es }}</td>
                    <td class="text-nowrap">{!! $ver($ficha->fe ? fecha($ficha->fe) : null, 'FE') !!}</td>
                    <td>{{ $ficha->ord }}</td>
                    <td>{{ $ficha->pl }}</td>
                    <td>{{ $ficha->ca }}</td>
                    <td>{{ $ficha->tb }}</td>
                    <td class="text-right">{!! $ver($ficha->k !== null ? numero($ficha->k) : null, 'K') !!}</td>
                    <td class="text-right">{!! $ver($ficha->p !== null ? numero($ficha->p, 2) : null, 'P') !!}</td>
                    <td class="text-right text-nowrap">{!! $ver($ficha->i !== null ? numero($ficha->i, 2) : null, 'I') !!}</td>
                    <td>{{ $ficha->d }}</td>
                    <td>{!! $ver($ficha->n, 'N') !!}</td>
                    <td class="text-nowrap">{!! $ver($ficha->fp ? fecha($ficha->fp) : null, 'FP') !!}</td>
                    <td>{!! $ver($ficha->pp, 'PP') !!}</td>
                    <td>{{ $ficha->o }}@if ($otras)<div class="text-muted small">{{ $otras }}</div>@endif</td>
                    <td>{{ $ficha->origen_tabla }}</td>
                  </tr>
                  @endforeach
                </table>

                {{ $fichas->appends(request()->query())->links() }}
                @endif

              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection