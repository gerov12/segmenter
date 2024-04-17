@extends('layouts.app')
@section ('content_main')
   <!-- Modal -->
   <div class="modal fade" id="operativoModal" role="dialog">
    <div class="modal-dialog">

     <!-- Modal content-->
     <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Info de operativo</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body" id="modal-body-loc">

      </div>
      <div class="modal-footer">
       <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
      </div>
     </div>
    </div>
   </div>

 <div class="container">
   <!-- Modal -->
   <div class="modal fade" id="operativoModal" role="dialog">
    <div class="modal-dialog">

     <!-- Modal content-->
     <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Detalle operativo</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body" id="modal-body-segmenta">

      </div>
      <div class="modal-footer">
       <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
      </div>
     </div>
    </div>
   </div>

   <h2>Listado de operativos</h2>
   <br>
  <div class="row">
    <div class="form-group col-md-6">
    <h5>Código<span class="text-danger"></span></h5>
     <div class="controls">
	<input type="numeric" name="codigo" id="codigo" class="form-control " placeholder="Por favor introduzca un código">
        <div class="help-block"></div>
     </div>
    </div>
    <div class="text-left" style="margin-left: 15px;">
     <button type="text" id="btnFiterSubmitSearch" class="btn btn-info">Buscar</button>
    </div>
   </div>
   <div class="row">
   <div class="col-sm-12">
    <table class="table table-striped table-bordered dataTable table-hover order-column " id="laravel_datatable_locas">
       <thead>
          <tr>
             <th>Id</th>
             <th>Nombre</th>
             <th>Descripcion</th>
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
      var table =  $('#laravel_datatable_operativos').DataTable({
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
          url: "{{ url('operativos-list') }}",
          type: 'POST',
          data: function (d) {
           d.codigo = $('#codigo').val();
          }
         },
         columns: [
                  { searchable: false, visible: false, data: 'id', name: 'id' },
                  { data: 'id', name: 'id' },
                  { data: 'nombre', name: 'nombre' },
                  { data: 'descripcion', name: 'descripcion' },
               ],
      });

    table.on( 'click', 'tr', function (e) {
        var data = table.row( this ).data();
        var url = "{{ url('operativo') }}"+"/"+data.id;
        $(location).attr('href',url);
   });


  $('#btnFiterSubmitSearch').click(function(){
  $('#laravel_datatable_operativos').DataTable().draw(true);
  });

} );

</script>
 @endsection
<?php // </body> </html> ?>
