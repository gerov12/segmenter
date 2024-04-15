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
    tr {
      transition: background-color 1s;
    }

  </style>
  <!-- Modal info archivo -->
  <div class="modal fade" id="empModal" role="dialog">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
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
      <button id="checksum-button" type="button" class="btn-sm btn-success" data-dismiss="modal"></button>
      <button id="close-button-check" type="button" class="btn-sm btn-primary float-right btn-detalles" data-dismiss="modal">Cerrar</button>
      <button id="back-button-check" class="btn-sm btn-primary float-right btn-detalles" data-target="#empModal" data-toggle="modal" data-dismiss="modal">Volver</button>
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
      <div class="modal-footer">
        <!-- al botón se le carga la ruta correspondiente en el script y se muestra si corresponde -->
        <button id="delete-copies-button" type="button" class="btn-sm btn-danger" data-dismiss="modal">Limpiar copias</button>
        <button id="close-button-copias" type="button" class="btn-sm btn-primary float-right btn-detalles" data-dismiss="modal">Cerrar</button>
        <button id="back-button-copias" class="btn-sm btn-primary float-right btn-detalles" data-target="#empModal" data-toggle="modal" data-dismiss="modal">Volver</button>
      </div>
    </div>
  </div>
  </div>

  <!-- Modal original de archivo copiado -->
  <div class="modal fade" id="originalModal" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title"></h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body" id="body-modal-original">
        <div id="aclaracion-original">
          <!-- acá se carga la aclaración de qué hace el botón limpiar -->
        </div>
        <br>
        <table class="table table-bordered" id="tabla-original">
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Creación</th>
              <th>Cargado por</th>
            </tr>
          </thead>
          <tbody>
            <!-- acá se cargará la info del archivo original -->
          </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <!-- al botón se le carga la ruta correspondiente en el script y se muestra si corresponde -->
        <button id="delete-copy-button" type="button" class="btn-sm btn-danger" data-dismiss = modal>Limpiar copia</button>
        <button id="close-button-original" type="button" class="btn-sm btn-primary float-right btn-detalles" data-dismiss="modal">Cerrar</button>
        <button id="back-button-original" class="btn-sm btn-primary float-right btn-detalles" data-target="#empModal" data-toggle="modal" data-dismiss="modal">Volver</button>
      </div>
    </div>
  </div>
  </div>

  <div class="container">
    <div id="alert-container">
      @if(Session::has('message'))
        <div class="alert alert-danger alert-dismissible" role="alert">
          <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          {{Session::get('message')}}
        </div>
      @endif
    </div>
    <h2>Listado de Archivos</h2>
    <div id="botones-problemas">
      @if($count_archivos_repetidos > 0)
        <h4><a id="count_repetidos" href="{{ route('archivos_repetidos') }}" class="badge badge-pill badge-warning"><i class="bi bi-copy mr-2"></i>Ver archivos repetidos ({{$count_archivos_repetidos}})</a></h4>
      @else
        <h4><a id="count_repetidos" style="display:none" href="{{ route('archivos_repetidos') }}" class="badge badge-pill badge-warning"><i class="bi bi-copy mr-2"></i>Ver archivos repetidos ({{$count_archivos_repetidos}})</a></h4>
      @endif

      @if($count_null_checksums > 0)
        <h4><a id="count_null" href="{{ route('checksums_no_calculados') }}" class="badge badge-pill badge-checksum"><i class="bi bi-exclamation-triangle mr-2"></i>Ver checksums no calculados ({{$count_null_checksums}})</a></h4>
      @else
        <h4><a id="count_null" style="display:none" href="{{ route('checksums_no_calculados') }}" class="badge badge-pill badge-checksum"><i class="bi bi-exclamation-triangle mr-2"></i>Ver checksums no calculados ({{$count_null_checksums}})</a></h4>
      @endif

      @if($count_error_checksums > 0)
        <h4><a id="count_error" href="{{ route('checksums_erroneos') }}" class="badge badge-pill badge-danger"><i class="bi bi-x-circle mr-2"></i>Ver checksums con error ({{$count_error_checksums}})</a></h4>
      @else
        <h4><a id="count_error" style="display:none" href="{{ route('checksums_erroneos') }}" class="badge badge-pill badge-danger"><i class="bi bi-x-circle mr-2"></i>Ver checksums con error ({{$count_error_checksums}})</a></h4>
      @endif

      @if($count_old_checksums > 0)
        <h4><a id="count_old" href="{{ route('checksums_obsoletos') }}" class="badge badge-pill badge-danger"><i class="bi bi-calendar-x mr-2"></i>Ver checksums obsoletos ({{$count_old_checksums}})</a></h4>
      @else
        <h4><a id="count_old" style="display:none" href="{{ route('checksums_obsoletos') }}" class="badge badge-pill badge-danger"><i class="bi bi-calendar-x mr-2"></i>Ver checksums obsoletos ({{$count_old_checksums}})</a></h4>
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
              <th>Cargado por</th>
              <th title="Observadores" alt="Observadores" ><i class="bi bi-eye-fill" style="font-size:15px"></i></th>
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
         serverSide: false,
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
            $
          }
        });
        console.log( 'You clicked on '+data.id+'\'s row' );
    }
   });

   // funcion abrir modal de checksum
   $(document).on('click', '#btn-checksum', function(event) {
        var button = $(this); // botón que activó el modal
        var file_id = button.data('file'); 
        var nombre_original = button.data('name'); 
        var status = button.data('status'); 
        var recalculable = button.data('recalculable'); 
        var info = button.data('info');
        var modal = $("#checksumModal");
        modal.find('.modal-title').text('Info sobre checksum (' + nombre_original + ')');

        var modalBody = modal.find('.modal-body');
        var modalfooter = modal.find('.modal-footer');
        var botonRecalcular = modalfooter.find('#checksum-button');
        var botonCerrar = modalfooter.find('#close-button-check');
        var botonVolver = modalfooter.find('#back-button-check');

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
            modalBody.find('#checksum-modal-info-2').text('Se recomienda revisar el archivo y, si el contenido es el adecuado, recalcular el checksum para comprobar su correctitud');
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

        // agrego el campo archivo-id al botón para recuperar en la animación
        botonRecalcular.data('archivo-id', file_id);

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

        if (info === true) {
          // si vengo de info permito volver
          botonVolver.css('display', 'block');
          botonCerrar.css('display', 'none');
        } else {
          // sino permito cerrar
          botonVolver.css('display', 'none');
          botonCerrar.css('display', 'block');
        }

        // Actualizar la ruta del botón de descarga del archivo
        modalfooter.find("#checksum-file-download-button").off('click').on('click', function() {
            var url = "{{ url('archivo/') }}"+"/"+file_id+"/descargar";
            $(location).attr('href', url);
        });
        modal.modal('show');
    });

    // confirm para recalcular/sincronizar desde el modal
    $('#checksum-button').on('click', function(event) {
        event.preventDefault(); // evita que el botón dirija directamente a su href
        var buttonText = $(this).text().trim();
        var message = "";
        var archivo_id = $(this).data('archivo-id');

        if (buttonText === "Sincronizar Checksum") {
            message = '¿Estás seguro de que deseas sincronizar el checksum?';
        } else if (buttonText === "Recalcular Checksum") {
            message = '¿Estás seguro de que deseas recalcular el checksum?';
        }

        if (message !== "" && confirm(message)) {
          $.ajax({
            url: $(this).attr("href"),
            type: 'POST',
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
                var table = $('#laravel_datatable').DataTable();
                var rowNode;
                table.rows().every(function () {
                    var data = this.data();
                    if (data.id === archivo_id) {
                        rowNode = this.node();
                        return false;
                    }
                });
                var row = $(rowNode);
                var originalColor = row.css('background-color');
                row.css('background-color', 'lightgreen');
                row.find('td:eq(6)').css('filter', 'blur(2px)');
                setTimeout(function() {
                  row.css('background-color', originalColor);
                }, 1500);
                setTimeout(function() {
                  table.ajax.reload();
                }, 2000);
                updateCounts();
              }
            }
          });
        }
    });

    //función abrir modal de copias
    $(document).on('click', '#btn-ver-copias', function(event) {
        var button = $(this); // botón que activó el modal
        var name = button.data('name');
        var modal = $('#copiasModal');
        var archivo = button.data('archivo');
        var info = button.data('info');
        var limpiables = button.data('limpiables');
        var modalfooter = modal.find('.modal-footer');
        var botonLimpiar = modalfooter.find('#delete-copies-button');
        var botonCerrar = modalfooter.find('#close-button-copias');
        var botonVolver = modalfooter.find('#back-button-copias');
        $.ajax({
            url: '/archivo/' + archivo + '/copias',
            type: 'GET',
            dataType: 'json',
            success: function(response){
                if (response) {
                  modal.find('.modal-title').text('Copias del archivo ' + name);

                  // obtengo el listado de copias de este archivo
                  var copias = response;

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

                  // agrego el campo archivo-id al botón para recuperar en la animación
                  botonLimpiar.data('archivo-id', archivo);
                  // si tengo los permisos necesarios
                  if (limpiables) {
                    // hago visible el botón
                    botonLimpiar.css('display', 'block');
                  } else {
                    // lo oculto
                    botonLimpiar.css('display', 'none');
                  }

                  if (info === true) {
                    // si vengo de info permito volver
                    botonVolver.css('display', 'block');
                    botonCerrar.css('display', 'none');
                  } else {
                    // sino permito cerrar
                    botonVolver.css('display', 'none');
                    botonCerrar.css('display', 'block');
                  }
                  modal.modal('show');
                };
            }
        })
    });

    // confirm para limpiar copias de un archivo desde el modal
    $('#delete-copies-button').on('click', function(event) {
        var archivo_id = $(this).data('archivo-id');
        if (confirm('Al confirmar se eliminarán las copias listadas y sus usuarios pasarán a ser "observadores" de este archivo. ¿Estás seguro?')) {
          $.ajax({
              url: 'archivos/limpiar/' + archivo_id + '/copias',
              type: 'POST',
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
                  var table = $('#laravel_datatable').DataTable();
                  var copias = response.id_copias;
                  var rowNodeOriginal;
                  var rows_copias = [];
                  table.rows().every(function() {
                      var data = this.data();
                      if (copias.includes(data.id)) {
                          rows_copias.push(this.node());
                      } else if (data.id === archivo_id) {
                        rowNodeOriginal = this.node();
                      }
                  });
                  rows_copias.forEach(function(rowNodeCopia) {
                    if (typeof rowNodeCopia !== 'undefined') { //sirve para el caso en el cual las copias están en otra página
                      var rowCopia = $(rowNodeCopia);
                      rowCopia.fadeOut(1000, function() {
                          rowCopia.remove();
                      });
                    }
                  });

                  var rowOriginal = $(rowNodeOriginal);
                  rowOriginal.find('td:eq(6)').css('filter', 'blur(2px)');
                  setTimeout(function() {
                    table.ajax.reload();
                  }, 1000);
                  updateCounts();
                }
              }
          });
        }
    });

    //función abrir modal de archivo original
    $(document).on('click', '#btn-ver-original', function(event) {
        var button = $(this); // botón que activó el modal
        var name = button.data('name');
        var modal = $('#originalModal');
        var archivo = button.data('archivo');
        var owner = button.data('owner');
        var info = button.data('info');
        var limpiable = button.data('limpiable');
        var modalfooter = modal.find('.modal-footer');
        var botonLimpiar = modalfooter.find('#delete-copy-button');
        var botonCerrar = modalfooter.find('#close-button-original');
        var botonVolver = modalfooter.find('#back-button-original');
        $.ajax({
            url: '/archivo/' + archivo + '/original',
            type: 'GET',
            dataType: 'json',
            success: function(response){
                if (response) {
                  modal.find('.modal-title').text('Original del archivo ' + name);

                  // obtengo el original de este archivo
                  var original = response;
                  console.log(original.nombre_original);
                  // contruyo la tabla para la info del original
                  var tableBody = '';
                  tableBody += '<tr>';
                  tableBody += '<td>' + original.nombre_original + '</td>';
                  tableBody += '<td>' + original.fecha + '</td>';
                  tableBody += '<td>' + original.user.name + '</td>';
                  tableBody += '</tr>';
                  $('#tabla-original tbody').html(tableBody);
                  $('#aclaracion-original').html('Al clickear en <span style="color:red">"Limpiar copia"</span> el usuario <b>' + owner + '</b> pasará a ser "observador" del siguiente archivo, eliminando el actual.');

                  // agrego los campos archivo-id y original-id al botón para recuperar en la animación
                  botonLimpiar.data('archivo-id', archivo);
                  botonLimpiar.data('original-id', original.id);
                  // si tengo los permisos necesarios
                  if (limpiable) {
                    // hago visible el botón
                    botonLimpiar.css('display', 'block');
                  } else {
                    // lo oculto
                    botonLimpiar.css('display', 'none');
                  }

                  if (info === true) {
                    // si vengo de info permito volver
                    botonVolver.css('display', 'block');
                    botonCerrar.css('display', 'none');
                  } else {
                    // sino permito cerrar
                    botonVolver.css('display', 'none');
                    botonCerrar.css('display', 'block');
                  }
                  modal.modal('show');
                };
            }
        })
    });

    // confirm para limpiar copia desde el modal
    $('#delete-copy-button').on('click', function(event) {
      var archivo_id = $(this).data('archivo-id');
      var original_id = $(this).data('original-id');
      if (confirm('Al confirmar se eliminará este archivo y pasarás a ser "observador" del archivo original. ¿Estás seguro?')) {
        $.ajax({
            url: 'archivos/limpiar/' + archivo_id,
            type: 'POST',
            data: {
              type: "individual"
            },
            success: function(response) {
              console.log(response);
              var alertClass = (response.statusCode == 200) ? 'alert-success' : 'alert-danger';
              var alertHtml = '<div class="alert ' + alertClass + ' alert-dismissible" role="alert">' +
                                '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                                response.message +
                              '</div>';
              $('#alert-container').html(alertHtml);
              if (response.statusCode == 200) {
                var table = $('#laravel_datatable').DataTable();
                var rowNodeOriginal;
                var rowNodeCopia;
                table.rows().every(function() {
                    var data = this.data();
                    if (data.id === archivo_id) {
                      rowNodeCopia = this.node();
                    } else if (data.id === original_id) {
                      rowNodeOriginal = this.node();
                    }
                });
                var rowCopia = $(rowNodeCopia);
                var rowOriginal = $(rowNodeOriginal);
                rowOriginal.find('td:eq(6)').css('filter', 'blur(2px)');
                rowCopia.fadeOut(1000, function() {
                    rowCopia.remove();
                    table.ajax.reload();
                });
                updateCounts();
              }
            }
        });
      }
    });

  // Funcion de botón Ver.
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

  // Función de botón Procesar.
  table.on('click', '.btn_arch_pasar', function () {
      var row = $(this).closest('tr');
      var data = table.row( row ).data();
      console.log('Pasar Datos desde Archivo: '+data.codigo);
        if (typeof data !== 'undefined') {
            url= "{{ url('archivo') }}"+"/"+data.id+"/pasar_data";
            $(location).attr('href',url);
           };
    });

  function updateCounts() {
    var token = $('meta[name="csrf-token"]').attr('content');
    $.ajax({
      url: "{{ route('contar_archivos') }}",
      method: "GET",
      success: function(data) {
        console.log(data);
        if (data.repetidos > 0) {
            $('#count_repetidos').html('<i class="bi bi-copy mr-2"></i>Ver archivos repetidos (' + data.repetidos + ')');
            $('#count_repetidos').css('display', 'inline-block')
        } else {
          $('#count_repetidos').css('display', 'none')
        }
        if (data.null > 0) {
            $('#count_null').html('<i class="bi bi-exclamation-triangle mr-2"></i>Ver checksums no calculados (' + data.null + ')');
            $('#count_null').css('display', 'inline-block')
        } else {
          $('#count_null').css('display', 'none')
        }
        if (data.error > 0) {
            $('#count_error').html('<i class="bi bi-x-circle mr-2"></i>Ver checksums con error (' + data.error + ')');
            $('#count_error').css('display', 'inline-block')
        } else {
          $('#count_error').css('display', 'none')
        }
        if (data.old > 0) {
            $('#count_old').html('<i class="bi bi-calendar-x mr-2"></i>Ver checksums obsoletos (' + data.old + ')');
            $('#count_old').css('display', 'inline-block')
        } else {
          $('#count_old').css('display', 'none')
        }
      }
    });
  }

} );
</script>
@endsection
