@extends('layouts.app')

@section('content')
<div class="container">
<div id="alert-container"></div>
<h2>Listado de archivos repetidos </h2>
  @if(count($repetidos) > 0)
  <h4><button id="bulk-button" onclick="return confirmarLimpiezaBulk()" class="btn btn-danger"> Limpiar ({{$owned}})</button></h4>
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
          <table class="table table-bordered" id="tabla-repetidos">
            <thead>
              <tr>
                <th>Nombre original</th>
                <th>Nombre copia</th>
                <th>Creación original</th>
                <th>Creación copia</th>
                <th>Dueño original</th>
                <th>Dueño copia</th>
                <th>*</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($repetidos as $archivo)
              <tr id="{{$archivo[1]->id}}">
                <td>{{$archivo[0]->nombre_original}}</td>
                <td>{{$archivo[1]->nombre_original}}</td>
                <td>{{$archivo[0]->created_at->format('d-M-Y')}}</td>
                <td>{{$archivo[1]->created_at->format('d-M-Y')}}</td>
                <td>{{$archivo[0]->user->name}}</td>
                <td>{{$archivo[1]->user->name}}</td>
                @if ($archivo[0]->ownedByUser(Auth::user()) || $archivo[1]->ownedByUser(Auth::user()) || Auth::user()->can('Administrar Archivos', 'Ver Archivos'))
                <td style="text-align: center;"><button onclick="return confirmarLimpieza({{$archivo[1]->id}})" class="btn btn-danger"> Limpiar </button></td>
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
  $('#tabla-repetidos').DataTable({
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
  function confirmarLimpieza(archivo_id){
    if (confirm('¿Estás seguro de que deseas eliminar el archivo repetido?. El usuario que cargó la copia pasará a ser "observador" del orignal. Esta acción es irreversible.')) {
      $.ajax({
        url: '/archivos/limpiar/' + archivo_id,
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
            var table = $('#tabla-repetidos').DataTable();
            row.fadeOut(1000, function() {
                table.row(row).remove();
                updateCount("repetidos");
                table.draw();
            }); 
          }
        }
      })
    }
  };

  function confirmarLimpiezaBulk(){
    if (confirm("¿Estás seguro de que deseas limpiar todos los archivos repetidos? Esta acción es irreversible.")) {
      $.ajax({
        url: '/archivos/limpiar/',
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
            var table = $('#tabla-repetidos').DataTable();
            $('#tabla-repetidos tbody tr').each(function() {
              var row = $(this);
              var rowId = row.attr('id');
              if (response.done_files.includes(Number(rowId))) {
                row.fadeOut(1000, function() {
                    table.row(row).remove();
                    updateCount("repetidos");
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
            $('#bulk-button').text('Limpiar (' + data + ')');
            $('#bulk-button').css('display', 'inline-block')
        } else {
          $('#bulk-button').css('display', 'none')
        }
      }
    });
  }
</script>
@endsection