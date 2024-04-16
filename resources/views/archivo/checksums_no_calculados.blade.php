@extends('layouts.app')

@section('content')
<div class="container">
<div id="alert-container"></div>
<h2>Listado de archivos con checksums no calculados con el nuevo método</h2>
  @if(count($checksums_no_calculados) > 0)
    <h4><button id="bulk-button" onclick="return confirmarCalculoBulk()" class="btn btn-success"> Recalcular ({{$owned}})</button></h4>
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
          <table class="table table-bordered" id="tabla-no-calculados">
            @if(count($checksums_no_calculados) > 0)
            <thead>
              <tr>
                <th>Nombre</th>
                <th>Creación</th>
                <th>Cargador</th>
                <th>Checksum</th>
                <th>*</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($checksums_no_calculados as $archivo)
              <tr id="{{$archivo->id}}">
                <td>{{$archivo->nombre_original}}</td>
                <td>{{$archivo->created_at->format('d-M-Y')}}</td>
                <td>{{$archivo->user->name}}</td>
                <td>{{$archivo->checksum}}</td>
                @if ($archivo->ownedByUser(Auth::user()) || Auth::user()->can('Administrar Archivos', 'Ver Archivos'))
                <td style="text-align: center;"><button onclick="return confirmarCalculo({{$archivo->id}})" class="btn btn-success"> Recalcular </button></td>
                @else
                <td style="text-align: center;"><i class="bi bi-ban"></i></td>
                @endif
              </tr>
              @endforeach
            </tbody>
            @else
            <h2>No hay checksums no calculados</h2>
            @endif
          </table>
        </div>
      </div>
    </div>
	</div>
</div>

@endsection
@section('footer_scripts')
<script>
  $('#tabla-no-calculados').DataTable({
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
  function confirmarCalculo(archivo_id){
    if (confirm("¿Estás seguro de que deseas recalcular el checksum? Esta acción es irreversible.")){
      $.ajax({
        url: '/archivos/recalcular_cs/' + archivo_id,
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
            row.fadeOut(1000, function() {
              row.remove();
            });
            updateCount("no_calculados");
            if ($('#tabla-no-calculados tbody').children().length == 0) {
              $('#tabla-no-calculados').DataTable().destroy();
              $('#tabla-no-calculados').html('<h2>Se resolvieron todos los casos :D</h2>');
            }
          }
        }
      })
    }
  };
  function confirmarCalculoBulk(){
    if (confirm("¿Estás seguro de que deseas recalcular los checksums? Esta acción es irreversible.")){
      $.ajax({
        url: '/archivos/recalcular_cs/',
        type: 'POST',
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        data: {
          type: "bulk",
          status: "no_calculados"
        },
        success: function(response) {
          var alertClass = (response.statusCode == 200) ? 'alert-success' : 'alert-danger';
          var alertHtml = '<div class="alert ' + alertClass + ' alert-dismissible" role="alert">' +
                            '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                            response.message +
                          '</div>';
          $('#alert-container').html(alertHtml);
          if (response.statusCode == 200) {
            $('#tabla-no-calculados tbody tr').each(function() {
                var row = $(this);
                row.fadeOut(1000, function() {
                    row.remove();
                });
            });
            updateCount("no_calculados");
            if ($('#tabla-no-calculados tbody').children().length == 0) {
              $('#tabla-no-calculados').DataTable().destroy();
              $('#tabla-no-calculados').html('<h2>Se resolvieron todos los casos :D</h2>');
            }
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
        console.log(data);
        if (data > 0) {
            $('#bulk-button').text('Recalcular (' + data + ')');
            $('#bulk-button').css('display', 'inline-block')
        } else {
          $('#bulk-button').css('display', 'none')
        }
      }
    });
  }
</script>
@endsection