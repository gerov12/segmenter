@extends('layouts.app')

@section ('content_main')
  <style>
    .badge-checksum {
    color: black;
    background-color: orange;
    }
    .badge-checksum:hover {
    color: black;
    }
    .grid-container {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    text-align: center;
    justify-content: center;
    } 
    .grid-item {
    padding: 10px;
    text-align: center;
    }
  </style>
  <!-- Modal info archivo -->
  <div class="modal fade" id="empModal" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
    <div class="modal-header">
      <h4 class="modal-title">Info de Archivo</h4>
      <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>
    <div class="modal-body">
    </div>
    <div class="modal-footer">
      <button type="button" class="btn-sm btn-primary float-right btn-detalles" data-dismiss="modal">Cerrar</button>
    </div>
    </div>
  </div>
  </div>

  <!-- Modal checksum -->
  <div class="modal fade" id="checksumModal" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
    <div class="modal-header">
      <h4 class="modal-title">
        <!-- acá se carga el título -->
      </h4>
      <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>
    <div class="modal-body">
      <!-- acá se carga el mensaje -->
      <h5 id="checksum-message" style="text-align: center"></h5>
      <br>
      <div id="checksum-modal-info-1" style="text-align: center; font-size: 16px"></div> 
      <br>
      <div id="checksum-modal-info-2" style="text-align: center; font-size: 14px"></div> 
    </div>
    <div class="modal-footer">
      <!-- se muestra botón descargar si corresponde -->
      <button id="checksum-file-download-button" type="button" class="btn_descarga btn-sm btn-secondary" > Descargar Archivo </button>
      <!-- al botón se le carga la ruta correspondiente en el script y se muestra si corresponde -->
      <button id="checksum-button" type="button" class="btn-sm btn-success"></button>
      <button type="button" class="btn-sm btn-primary float-right btn-detalles" data-dismiss="modal">Cerrar</button>
    </div>
    </div>
  </div>
  </div>

  <!-- Modal copias de archivo -->
  <div class="modal fade" id="copiasModal" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
    <div class="modal-header">
      <h4 class="modal-title"></h4>
      <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>
    <div class="modal-body">
      <table class="table table-bordered" id="tabla-repetidos">
        <thead>
          <tr>
            <th>Nombre</th>
            <th>Creación</th>
            <th>Cargado por</th>
          </tr>
        </thead>
        <tbody>
          <!-- acá se cargará la info de las copias -->
        </tbody>
      </table>
    </div>
    </div>
  </div>
  </div>

  <div class="container">
    @if(Session::has('message'))
      <div class="alert alert-danger alert-dismissible" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        {{Session::get('message')}}
      </div>
    @endif
    <h2>Listado de Archivos</h2>
    <div id="botones-problemas">
      @if($count_archivos_repetidos > 0)
      <h4><a href="{{ route('archivos_repetidos') }}" class="badge badge-pill badge-warning"><i class="bi bi-copy mr-2"></i>Ver archivos repetidos ({{$count_archivos_repetidos}})</a></h4>
      @endif 
      @if($count_null_checksums > 0)
        <h4><a href="{{ route('checksums_no_calculados') }}" class="badge badge-pill badge-checksum"><i class="bi bi-exclamation-triangle mr-2"></i>Ver checksums no calculados ({{$count_null_checksums}})</a></h4>
      @endif
      @if($count_error_checksums > 0) 
        <h4><a href="{{ route('checksums_erroneos') }}" class="badge badge-pill badge-danger"><i class="bi bi-x-circle mr-2"></i>Ver checksums con error ({{$count_error_checksums}})</a></h4>
      @endif 
      @if($count_old_checksums > 0) 
        <h4><a href="{{ route('checksums_obsoletos') }}" class="badge badge-pill badge-danger"><i class="bi bi-calendar-x mr-2"></i>Ver checksums obsoletos ({{$count_old_checksums}})</a></h4>
      @endif 
    </div>
    <br>

    <div class="col-lg-12">
    <table class="table table-striped table-bordered dataTable table-hover order-column" id="laravel_datatable">
        <thead>
          <tr>
              <th>Id</th>
              <th>Nombre</th>
              <th>Id-Nombre</th>
              <th>Usuario</th>
              <th>Tipo</th>
              <th>Mime</th>
              <th>Checksum</th>
              <th>Tamaño</th>
              <th>Creación</th>
              <th>Cargador</th>
              <th alt="Observadores" >(o)</th>
              <th>Estado</th>
              <th> * </th>
          </tr>
        </thead>
    </table>
    </div>
    </div>
  </div>
@endsection
@section('footer_scripts')  
 <script>
 $(document).ready( function () {
     $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
      });
      var table =  $('#laravel_datatable').DataTable({
        "pageLength": 10,
         language: //{url:'https://cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json'},
{
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
},
         processing: true,
         serverSide: true,
         ajax: {
          url: "{{ url('archivos') }}",
          type: 'GET',
          data: function (d) {
          d.codigo = $('#nombre').val();
          }
         },
         columns: [
                  { visible: false, data: 'id', name: 'id' },
                  { data: 'nombre_original', name: 'nombre_original' },
                  { visible: false, data: 'nombre', name: 'nombre' },
                  { visible: false, data: 'user_id', name: 'user_id' },
                  { data: 'tipo', name: 'tipo' },
                  { visible: false, data: 'mime', name: 'mime' },
                  { visible: false, data: 'checksum', name: 'checksum' },
                  { data: 'size_h', name: 'size'},
                  { data: 'created_at_h', name: 'created_at'},
                  { data: 'usuario', name: 'usuario' },
                  { data: 'viewers_count', name: 'viewers_count' },
                  { data: 'status', name: 'status' },
                  { data: 'action', name: 'action', orderable: false}
        ]
      });

  // funcion abrir info archivo al clickear fila
   table.on( 'click', 'tr', function (e) {
    if ($(e.target).closest('button').length === 0) {
      var data = table.row( this ).data();
      // AJAX request
        $.ajax({
          url: "{{ url('archivo') }}"+"\\"+data.id,
          type: 'post',
          data: {id: data.id,format: 'html'},
          success: function(response){ 
            // Add response in Modal body
            $('#empModal .modal-body').html(response);

            // Display Modal
            $('#empModal').modal('show'); 
          }
        });
        console.log( 'You clicked on '+data.id+'\'s row' );
    }
   });

   // funcion abrir modal de checksum
   $('#checksumModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget); // botón que activó el modal
        var file_id = button.data('file'); 
        var nombre_original = button.data('name'); 
        var status = button.data('status'); 
        var recalculable = button.data('recalculable'); 

        $(this).find('.modal-title').text('Info sobre checksum (' + nombre_original + ')');

        var modalBody = $(this).find('.modal-body');
        var modalfooter = $(this).find('.modal-footer');
        var botonRecalcular = modalfooter.find('#checksum-button');

        if (status === 'no_check') {
          // actualizo mensaje principal
          modalBody.find('#checksum-message').text('El checksum de este archivo no fue recalculado con el nuevo método.');
          // actualizo info 1
          var info1 = modalBody.find('#checksum-modal-info-1');
          info1.empty(); // limpio el contenido anterior
          info1.append('<div style="margin-bottom: 10px;">Esto imposibilita la correcta detección de</div>');
          // agrego el grid-container
          var gridContainer = $('<div class="grid-container"></div>');
          var gridItem1 = $('<div class="grid-item"></div>').append('<i class="bi bi-file-earmark-excel"></i><br><span class="badge badge-pill badge-danger" style="font-size: 13px">Datos erroneos</span>');
          var gridItem2 = $('<div class="grid-item"></div>').append('<i class="bi bi-copy"></i><br><span class="badge badge-pill badge-warning" style="font-size: 13px">Contenido duplicado</span>');
          gridContainer.append(gridItem1, gridItem2);
          // agrego el grid-container a info1
          info1.append(gridContainer);
          info1.append('<div>en la Base de Datos.</div>');
          // actualizo info 2
          modalBody.find('#checksum-modal-info-2').text('Debe recalcularse para comprobar su correctitud');
          // oculto botón descargar
          modalfooter.find("#checksum-file-download-button").css('display', 'none');
        } else if (status === 'wrong_check') {
            // actualizo mensaje principal
            modalBody.find('#checksum-message').text('Hay un error en el cálculo del checksum de este archivo.');
            // actualizo info 1
            var info1 = modalBody.find('#checksum-modal-info-1');
            info1.empty(); // Limpiar el contenido anterior
            info1.append('<div style="margin-bottom: 10px;">Esto puede deberse a</div>');
            info1.append('<div style="margin-bottom: 10px;"><i class="bi bi-file-earmark-excel"></i><br><span class="badge badge-pill badge-danger" style="font-size: 13px">Datos erroneos</span></div>');
            info1.append('<div>en la Base de Datos.</div>');
            info1.append('<br>');
            info1.append('<div style="margin-bottom: 10px;">Además imposibilita la detección de</div>');
            info1.append('<div style="margin-bottom: 10px;"><i class="bi bi-copy"></i><br><span class="badge badge-pill badge-warning" style="font-size: 13px">Archivos Duplicados</span></div>');
            info1.append('<br>');
            // actualizo info 2
            modalBody.find('#checksum-modal-info-2').text('Se recomienda revisar el archivo y recalcular el checksum para comprobar su correctitud');
            // muestro botón descargar
            modalfooter.find("#checksum-file-download-button").css('display', 'block');
          } else if (status === 'old_check') {
            // actualizo mensaje principal
            modalBody.find('#checksum-message').text('El cálculo del checksum de este archivo está obsoleto.');
            // actualizo info 1
            var info1 = modalBody.find('#checksum-modal-info-1');
            info1.empty(); // Limpiar el contenido anterior
            info1.append('<div style="margin-bottom: 10px;">Es decir que el checksum no coincide con el calculado mediante el nuevo método.</div>');
            info1.append('<div style="margin-bottom: 10px;">Esto imposibilita la correcta detección de</div>');
            // agrego el grid-container
            var gridContainer = $('<div class="grid-container"></div>');
            var gridItem1 = $('<div class="grid-item"></div>').append('<i class="bi bi-file-earmark-excel"></i><br><span class="badge badge-pill badge-danger" style="font-size: 13px">Datos erroneos</span>');
            var gridItem2 = $('<div class="grid-item"></div>').append('<i class="bi bi-copy"></i><br><span class="badge badge-pill badge-warning" style="font-size: 13px">Contenido duplicado</span>');
            gridContainer.append(gridItem1, gridItem2);
            // agrego el grid-container a info1
            info1.append(gridContainer);
            info1.append('<div>en la Base de Datos.</div>');
            // actualizo info 2
            modalBody.find('#checksum-modal-info-2').text('Se debe sincronizar el checksum del archivo con el nuevo cálculo');
            // actualizo la ruta del botón para que sea sincronizar
            botonRecalcular.attr('href', "{{ route('sincronizar_checksums', ':archivo_id') }}".replace(':archivo_id', file_id)).text("Sincronizar Checksum");
            // oculto botón descargar
            modalfooter.find("#checksum-file-download-button").css('display', 'none');
        }
        if (status !== 'old_check') {
          // actualizo la ruta del botón para que sea recalcular
          botonRecalcular.attr('href', "{{ route('recalcular_checksums', ':archivo_id') }}".replace(':archivo_id', file_id)).text("Recalcular Checksum");
        }   
       
        if (recalculable) {
          // hago visible el botón
          botonRecalcular.css('display', 'block');
        } else {
          // lo oculto
          botonRecalcular.css('display', 'none');
        }

        // Actualizar la ruta del botón de descarga del archivo
        modalfooter.find("#checksum-file-download-button").off('click').on('click', function() {
            var url = "{{ url('archivo/') }}"+"/"+file_id+"/descargar";
            $(location).attr('href', url);
        });
    });

    // confirm para recalcular desde el modal
    $('#checksum-button').on('click', function(event) {
        event.preventDefault(); // evita que el botón diriga directamente a su href
        if (confirm('¿Estás seguro de que deseas calcular el checksum?')) {
            window.location.href = $(this).attr('href');
        }
    });

    //función abrir modal de copias
    $('#copiasModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // botón que activó el modal
        var name = button.data('name');
        var modal = this;
        console.log(name);
        var archivo = button.data('archivo');
        $.ajax({
            url: 'archivo/' + archivo + '/copias',
            type: 'GET',
            dataType: 'json',
            success: function(response){
                if (response) {
                  $(modal).find('.modal-title').text('Copias del archivo ' + name);

                  // obtengo el listado de copias de este archivo
                  var copias = response;
                  console.log(copias);

                  // contruyo la tabla de copias
                  var tableBody = '';
                  copias.forEach(function(copia){
                      tableBody += '<tr>';
                      tableBody += '<td>' + copia.nombre_original + '</td>';
                      tableBody += '<td>' + copia.fecha + '</td>';
                      tableBody += '<td>' + copia.user.name + '</td>';
                      tableBody += '</tr>';
                  });
                  $('#tabla-repetidos tbody').html(tableBody);
                };
            }
        })
    });

  table.on('click', '.btn_arch', function () {
    var row = $(this).closest('tr');
    var data = table.row( row ).data();
    console.log('Ver Archivo: '+data.codigo);
      if (typeof data !== 'undefined') {
          url= "{{ url('archivo') }}"+"/"+data.id;
          $(location).attr('href',url);
          };
  });

// Función de botón Procesar.
    table.on('click', '.btn_arch_procesar', function () {
      var row = $(this).closest('tr');
      var data = table.row( row ).data();
      console.log('Procesar Archivo: '+data.codigo);
        if (typeof data !== 'undefined') {
            url= "{{ url('archivo') }}"+"/"+data.id+"/procesar";
            $(location).attr('href',url);
           };
    });

// Función de botón Descarga.
    table.on('click', '.btn_descarga', function () {
      var row = $(this).closest('tr');
      var data = table.row( row ).data();
      console.log('Descargando: '+data.codigo);
        if (typeof data !== 'undefined') {
            url= "{{ url('archivo/') }}"+"/"+data.id+"/descargar";
            $(location).attr('href',url);
           };
    });
  
// Función de botón Borrar.
    table.on('click', '.btn_arch_delete', function () {
      var $ele = $(this).parent().parent();
      var row = $(this).closest('tr');
      var data = table.row( row ).data();
      if (typeof data !== 'undefined') {
      $.ajax({
         url: "{{ url('archivo') }}"+"\\"+data.id,
         type: "DELETE",
	 data: {id: data.id,
                _token:'{{ csrf_token() }}'},
         success: function(response){ 
	     // Add response in Modal body
       if(response=='ok'){
        if(response.statusCode==200){
	          row.fadeOut().remove();
        }
        if(response.statusCode==405){
              alert("Error al intentar borrar");
        }
        if(response.statusCode==500){
              alert("Error al intentar borrar. En el servidor");
          }
        alert("Se eliminó el registro del archivo");
        row.fadeOut().remove();
        $('.modal-body').html(response);
       } else {
        alert("El archivo es utilizado por " + response + " usuario(s). No se eliminará");
       }
           }
      });
      };
    });

  // Función de botón Dejar de ver.
  table.on('click', '.btn_arch_detach', function () {
      var $ele = $(this).parent().parent();
      var row = $(this).closest('tr');
      var data = table.row( row ).data();
      if (typeof data !== 'undefined') {
      $.ajax({
         url: "{{ url('archivo') }}"+"\\"+data.id+"/detach",
         type: "PUT",
	 data: {id: data.id,
                _token:'{{ csrf_token() }}'},
         success: function(response){ 
	     // Add response in Modal body
	     if(response.statusCode==200){
	          row.fadeOut().remove();
	     }
	     if(response.statusCode==405){
	          alert("Error al intentar borrar");
	     }
      if(response.statusCode==500){
            alert("Error al intentar borrar. En el servidor");
        }
      alert("Ya no se visualizará el archivo");
	     row.fadeOut().remove();
	     $('.modal-body').html(response);

           }
      });
      };
    });
  
  $('#btnFiterSubmitSearch').click(function(){
     $('#laravel_datatable').DataTable().draw(true);
  });

} );
</script>
@endsection
