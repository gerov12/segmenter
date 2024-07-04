@extends('layouts.app')

@section('content_main')
    <style>
        .action-column {
            white-space: nowrap;
            text-align: center;
            justify-content: center;
            max-width: 150px;
        }
    </style>

    <!-- Modal info-->
    <div class="modal fade" id="empModal" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Info de operativo</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body info-modal-body">

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal nuevo-->
    <div class="modal fade" id="newOperativoModal" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Nuevo operativo</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form action="{{ route('operativo.crear') }}" method="POST" id="form-create-operativo">
                    @csrf
                    <div class="modal-body">
                        <label for="nameInput">Nombre</label>
                        <input type="text" class="form-control" id="nameInput" name="nombre">
                        @if ($errors->new->has('nombre'))
                            <div class="text-danger">{{ $errors->new->first('nombre') }}</div>
                        @endif
                        <label for="observationInput">Observación</label>
                        <input type="text" class="form-control" id="observationInput" name="observacion">
                        @if ($errors->new->has('observacion'))
                            <div class="text-danger">{{ $errors->new->first('observacion') }}</div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <input type="submit" name="btn" class="btn btn-primary btn-submit-create-operativo" value="Confirmar" onclick="return confirmarCreacion()">
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal editar-->
    <div class="modal fade" id="editOperativoModal" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Editar operativo</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form action="{{ route('operativo.editar') }}" method="POST" id="form-edit-operativo">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" id="operativoId" name="operativo_id">
                        <label for="editNameInput">Nombre</label>
                        <input type="text" class="form-control" id="editNameInput" name="nombre">
                        @if ($errors->edit->has('nombre'))
                            <div class="text-danger">{{ $errors->edit->first('nombre') }}</div>
                        @endif
                        <label for="editObservationInput">Observación</label>
                        <input type="text" class="form-control" id="editObservationInput" name="observacion">
                        @if ($errors->edit->has('observacion'))
                            <div class="text-danger">{{ $errors->edit->first('observacion') }}</div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <input type="submit" name="btn" class="btn btn-primary btn-submit-create-operativo" value="Confirmar" onclick="return confirmarEdicion()">
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="container">
      <div class="row">
      </div>
        <h4>Listado de operativos
            @can('Administrar Operativos')
                <button class="badge badge-pill badge-primary ml-2" data-toggle="modal" id="btn-trigger-modal-nuevo-operativo" data-target="#newOperativoModal">+ Nuevo</button>
            @endcan
        </h4>
        <div class="row">
            <div class="col-lg-12">
                <table
                    class="table table-sm table-striped table-bordered dataTable table-hover order-column table-condensed compact"
                    id="laravel_datatable">
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>Nombre</th>
                            <th>Observación</th>
                            <th>*</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection
@section('footer_scripts')
    <script>
        $(document).ready(function() {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    var table = $('#laravel_datatable').DataTable({
                        "pageLength": 25,
                        language: //{url:'https://cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json'},
                        {
                            "sProcessing": "Procesando...",
                            "sLengthMenu": "Mostrar _MENU_ registros",
                            "sZeroRecords": "No se encontraron resultados",
                            "sEmptyTable": "Ningún dato disponible en esta tabla =(",
                            "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                            "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
                            "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
                            "sInfoPostFix": "",
                            "sSearch": "Buscar:",
                            "sUrl": "",
                            "sInfoThousands": ",",
                            "sLoadingRecords": "Cargando...",
                            "oPaginate": {
                                "sFirst": "Primero",
                                "sLast": "Último",
                                "sNext": "Siguiente",
                                "sPrevious": "Anterior"
                            },
                            "oAria": {
                                "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
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
                            type: 'GET',
                            data: function(d) {
                                d.codigo = $('#codigo').val();
                            }
                        },
                        columns: [{
                                visible: false,
                                data: 'id',
                                name: 'id'
                            },
                            {
                                data: 'nombre',
                                name: 'nombre'
                            },
                            {
                                data: 'observacion',
                                name: 'observacion'
                            },
                            {
                                data: 'action',
                                name: 'action',
                                className: 'action-column',
                            },
                        ]
                    });

                    table.on('click', 'tr', function(e) {
                        if ($(e.target).closest('button').length === 0) {
                            var data = table.row(this).data();
                            if ((data != null)) {
                                // AJAX request
                                $.ajax({
                                    url: "{{ url('operativo') }}" + "/info/" + data.id,
                                    type: 'post',
                                    data: {
                                        id: data.id,
                                        format: 'html'
                                    },
                                    success: function(response) {
                                        // Add response in Modal body
                                        $('.info-modal-body').html(response);

                                        // Display Modal
                                        $('#empModal').modal('show');
                                    }
                                });
                                console.log('You clicked on ' + data.id + '\'s row');
                            }
                        }
                    });

                    // Función de botón Seleccionar
                    table.on('click', '.btn_seleccionar', function() {
                        var row = $(this).closest('tr');
                        var data = table.row(row).data();
                        console.log('Seleccionar operativo: ' + data.codigo);
                        if (typeof data !== 'undefined') {
                            url = "{{ url('operativo/seleccionar') }}" + "/" + data.id;
                            $(location).attr('href', url);
                        };
                    });

                    $('#btnFiterSubmitSearch').click(function() {
                        $('#laravel_datatable').DataTable().draw(true);
                    });

                    // Función de botón Borrar.
                    table.on('click', '.btn_op_delete', function() {
                        var $ele = $(this).parent().parent();
                        console.log($ele);
                        var row = $(this).closest('tr');
                        var data = table.row(row).data();
                        if ((typeof data !== 'undefined') &&
                            (confirm('El elemento “' + data.id +
                                    '” va a ser borrado de la tabla operativos, ¿es correcto? \n'+
                                    'Selecccionar el motivo por el cual se borra el elemento( '+
                                    'en este caso “Error de Ingreso”)' +
                                    ''))) {
                                    $.ajax({
                                        url: "{{ url('operativo') }}" + "\\" + data.id,
                                        type: "DELETE",
                                        data: {
                                            id: data.id,
                                            _token: '{{ csrf_token() }}'
                                        },
                                        success: function(response) {
                                            // Add response in Modal body
                                            if (response.statusCode == 200) {
                                                row.fadeOut().remove();
                                                alert("Se eliminó el registro de la operativo");
                                                $('.modal-body').html(response.message);
                                            } else if (response.statusCode == 405) {
                                                alert("Error al intentar borrar");
                                            } else if (response.statusCode == 500) {
                                                alert("Error al intentar borrar. En el servidor");
                                            } else {
                                                alert("Mensaje del Servidor: " + response.message);
                                            }
                                            console.log(response);
                                        }
                                    });
                                };
                            });

                    });

                    function confirmarCreacion(){
                        return confirm("¿Estás seguro de que deseas crear el nuevo operativo \"" + document.getElementById('nameInput').value +"\" ?");
                    };
                    function confirmarEdicion(){
                        return confirm("¿Estás seguro de que deseas editar el operativo \"" + document.getElementById('editNameInput').value +"\" ?");
                    };

                    function openEditModal(operativo) {
                        if (operativo) {
                            $('#operativoId').val(operativo.id);
                            $('#editNameInput').val(operativo.nombre);
                            $('#editObservationInput').val(operativo.observacion);
                            $('#editOperativoModal').modal('show');
                        }
                    }

                    $(document).on('click', '.editButton', function(){
                        var selectedOperativo = $(this).data('operativo');
                        console.log(selectedOperativo);
                        openEditModal(selectedOperativo);
                    });
                    

                    @if ($errors->hasBag('edit'))
                        $(document).ready(function() {
                            var operativoFromSession = {!! json_encode(session('operativo_en_edicion')) !!};
                            if (operativoFromSession) {
                                openEditModal(operativoFromSession);
                            }
                        });
                    @elseif ($errors->hasBag('new'))
                        $('#newOperativoModal').modal('show');
                    @endif
    </script>
@endsection
