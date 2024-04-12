@extends('layouts.app')

@section('content')
<div class="container">
<h2>Listado de archivos con checksums obsoletos </h2>
  @can('Administrar Archivos', 'Ver Archivos')
    @if(count($checksums_obsoletos) > 0)
    <h4><a href="{{route('sincronizar_checksums')}}" onclick="return confirmarSincronizacionBulk()" class="btn btn-success"> Sincronizar ({{count($checksums_obsoletos)}})</a></h4>
    @endif
  @endcan
  <br>
	<div class="row justify-content-center">
    <div class="card w-100">
      <div class="card-body">
        @if(Session::has('info'))
          <div class="alert alert-success alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            {{Session::get('info')}}
          </div>
        @endif
        <div class="table-responsive">
          <table class="table table-bordered" id="tabla-obsoletos">
            @if($checksums_obsoletos !== null)
            <thead>
              <tr>
                <th>Nombre</th>
                <th>Creación</th>
                <th>Cargador</th>
                <th>Checksum obsoleto</th>
                <th>Checksum recalculado</th>
                <th>*</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($checksums_obsoletos as $archivo)
              <tr>
                <td>{{$archivo['archivo']->nombre_original}}</td>
                <td>{{$archivo['archivo']->created_at->format('d-M-Y')}}</td>
                <td>{{$archivo['archivo']->user->name}}</td>
                <td>
                  {{$archivo['archivo']->checksum}} <br>
                  ({{$archivo['archivo']->updated_at->format('d-M-Y H:i:s')}})
                </td>
                <td>
                  {{$archivo['control']->checksum}} <br>
                  ({{$archivo['control']->updated_at->format('d-M-Y H:i:s')}})
                </td>
                @if ($archivo['archivo']->ownedByUser(Auth::user()) || Auth::user()->can('Administrar Archivos', 'Ver Archivos'))
                <td style="text-align: center;"><a href="{{route('sincronizar_checksums', ['archivo_id' => $archivo['archivo']->id])}}" onclick="return confirmarSincronizacion()" class="btn btn-success"> Sincronizar </a></td>
                @else
                <td style="text-align: center;"><i class="bi bi-ban"></i></td>
                @endif
              </tr>
              @endforeach
            </tbody>
            @else
            <h1>No hay checksums obsoletos</h1>
            @endif
          </table>
        </div>
      </div>
    </div>
	</div>
</div>

@endsection
@section('footer_scripts')
<script>src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"</script>
<script>src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"</script>
<script>
  $('#tabla-obsoletos').DataTable({
    language: {
      "sProcessing":     "Procesando...",
      "sLengthMenu":     "Mostrar _MENU_ registros",
      "sZeroRecords":    "No se encontraron resultados",
      "sEmptyTable":     "Ningún dato disponible en esta tabla =(",
      "sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
      "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
      "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
      "sInfoPostFix":    "",
      "sSearch":         "Buscar:",
      "sUrl":            "",
      "sInfoThousands":  ",",
      "sLoadingRecords": "Cargando...",
      "oPaginate": {
          "sFirst":    "Primero",
          "sLast":     "Último",
          "sNext":     "Siguiente",
          "sPrevious": "Anterior"
      },
      "oAria": {
          "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
          "sSortDescending": ": Activar para ordenar la columna de manera descendente"
      },
      "buttons": {
          "copy": "Copiar",
          "colvis": "Visibilidad"
      }
    }
  });
</script>
<script type="text/javascript">
  function confirmarSincronizacion(){
    return confirm("¿Estás seguro de que deseas sincronizar el checksum? Esta acción es irreversible.");
  };
  function confirmarSincronizacionBulk(){
    return confirm("¿Estás seguro de que deseas sincronizar los checksums? Esta acción es irreversible.");
  };
</script>
@endsection