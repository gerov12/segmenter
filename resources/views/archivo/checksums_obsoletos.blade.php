@extends('layouts.app')

@section('content')
<div class="container">
<div id="alert-container"></div>
<h2>Listado de archivos con checksums obsoletos </h2>
  @if(count($checksums_obsoletos) > 0 && $owned > 0)
  <h4><button id="bulk-button" onclick="return confirmarSincronizacionBulk()" class="btn btn-success"> Sincronizar ({{$owned}})</button></h4>
  @endif
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
              <tr id="{{$archivo['archivo']->id}}">
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
                <td style="text-align: center;"><button onclick="return confirmarSincronizacion({{$archivo['archivo']->id}})" class="btn btn-success"> Sincronizar </button></td>
                @else
                <td style="text-align: center;"><i class="bi bi-ban"></i></td>
                @endif
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
	</div>
</div>

@endsection
@section('footer_scripts')
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
  function confirmarSincronizacion(archivo_id){
    if (confirm("¿Estás seguro de que deseas sincronizar el checksum? Esta acción es irreversible.")) {
      $.ajax({
        url: '/archivos/sincronizar_cs/' + archivo_id,
        type: 'POST',
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        data: {
          type: "individual"
        },
        success: function(response) {
          var alertClass = (response.statusCode == 200) ? 'alert-success' : 'alert-danger';
          var alertHtml = '<div class="alert ' + alertClass + ' alert-dismissible" role="alert">' +
                            '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                            response.message +
                          '</div>';
          $('#alert-container').html(alertHtml);
          if (response.statusCode == 200) {
            var row = $('#'+archivo_id);
            var table = $('#tabla-obsoletos').DataTable();
            row.fadeOut(1000, function() {
                table.row(row).remove();
                updateCount("obsoletos");
                table.draw();
            }); 
          }
        }
      })
    }
  };

  function confirmarSincronizacionBulk(){
    if (confirm("¿Estás seguro de que deseas sincronizar los checksums? Esta acción es irreversible.")) {
      $.ajax({
        url: '/archivos/sincronizar_cs/',
        type: 'POST',
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        data: {
          type: "bulk"
        },
        success: function(response) {
          var alertClass = (response.statusCode == 200) ? 'alert-success' : 'alert-danger';
          var alertHtml = '<div class="alert ' + alertClass + ' alert-dismissible" role="alert">' +
                            '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                            response.message +
                          '</div>';
          $('#alert-container').html(alertHtml);
          if (response.statusCode == 200) {
            var table = $('#tabla-obsoletos').DataTable();
            $('#tabla-obsoletos tbody tr').each(function() {
              var row = $(this);
              var rowId = row.attr('id');
              if (response.done_files.includes(Number(rowId))) {
                row.fadeOut(1000, function() {
                    table.row(row).remove();
                    updateCount("obsoletos");
                    table.draw();
                });
              }
            });
          }
        }
      })
    }
  };

  function updateCount(estado) {
    var token = $('meta[name="csrf-token"]').attr('content');
    $.ajax({
      url: "{{ route('contar_owned') }}",
      method: "POST",
      headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
      data: {estado: estado},
      success: function(data) {
        if (data > 0) {
            $('#bulk-button').text('Sincronizar (' + data + ')');
            $('#bulk-button').css('display', 'inline-block')
        } else {
          $('#bulk-button').css('display', 'none')
        }
      }
    });
  }
</script>
@endsection