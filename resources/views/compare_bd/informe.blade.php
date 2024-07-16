@extends('layouts.app')

@section('content_main')
<style>
    .top-container {
        display: flex;
    }

    #info {
        flex: 3; /* toma el 75% del ancho */
    }

    #store {
        justify-content: right;
        align-items: top;
    }
    .highlight {
        background-color: #d4edda !important; /* verde claro */
    }
    .highlight-error {
        background-color: #ef5350 !important; /* rojo */
    }
    tr {
      transition: background-color 1s;
    }

</style>
<div class="container">

    <!-- Modal de confirmación -->
    <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="confirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmationModalLabel">Aclaración</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    La importación de geometrías es <b>posterior</b> a la generación del informe. <br> 
                    Por lo tanto, al <span style="color:blue">'Guardar informe'</span>, todas los elementos que no tenían geometría figurarán de esa forma en el mismo.
                </div>
                <div class="modal-footer">
                    <div class="form-check" style="flex-grow: 1; display: flex;">
                        <input type="checkbox" class="form-check-input" id="dontShowAgainCheckbox">
                        <label class="form-check-label" for="dontShowAgainCheckbox">No mostrar este mensaje de nuevo</label>
                    </div>
                    <div>
                        <button type="button" class="btn btn-primary" id="confirmButton">Aceptar</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="alert-container">
        @if(Session::has('message'))
            <div class="alert alert-danger alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            {{Session::get('message')}}
            </div>
        @endif
    </div>
    <div class="row justify-content-center"> 
        <div class="card" style="width: 120%">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    Resultado de la comparación de la capa <b>{{explode(':', $capa)[1] }}</b> del geoservicio 
                    @if ($geoservicio)
                        "<b>{{ $geoservicio->nombre }}</b>" 
                    @else
                        "<b>-</b>" 
                    @endif 
                    con la tabla <b>{{ $tabla }}</b> de la BD
                </div>
                @if (!$geoservicio->id)
                <span title="Conexión rápida" class="badge badge-pill badge-success ml-auto"><i class="bi bi-lightning-charge"></i></span>
                @endif
            </div>
            <div class="card-body">
                <div class="top-container">
                    <div id="info">
                        Informe realizado el <b>{{ $datetime->format('d-m-Y H:i:s') }}</b> por <b>{{ $usuario->name }}</b> <br>
                        Operativo: {{ $operativo }} <br>
                        Total de {{ strtolower($tabla) }}s con errores: {{ $elementos_erroneos }} <br>
                        Total de errores de validación: {{ $total_errores }} <br>
                    </div>
                    <div id="store">
                        @if ($tipo_informe == "resultado")
                        <button type="button" id="btn-guardar" class="btn btn-primary mr-2">
                            <span id="spinner" style="display: none;">
                                <div class="spinner-border spinner-border-sm" role="status">
                                    <span class="sr-only">Cargando...</span>
                                </div>
                            </span>
                            <span id="btn-text">Guardar informe</span>
                            <i class="bi bi-floppy ml-2"></i>
                        </button>
                        @can('Ver Informes')
                        <a type="button" href="{{ route('compare.informes') }}" class="btn btn-success mr-2">Ver informes</a>
                        @endcan
                        @elseif ($tipo_informe == "informe")
                        <a type="button" href="{{ route('compare.informes') }}" class="btn btn-success mr-2">Volver a informes</a>
                        @can('Generar Informes')
                        <a type="button" href="{{ route('compare.repetirInforme',$informe_id) }}" class="btn btn-success mr-2">Repetir informe</a>
                        @endcan
                        @endif
                        @can('Generar Informes')
                        <a type="button" href="{{ route('compare.geoservicios') }}" class="btn btn-info mr-2" style="color:white">Nueva comparación</a>
                        @endcan
                    </div>
                </div>
                <br>
                <table class="table table-bordered" id="tabla-resultado" style="width:100%">
                    <thead>
                        <tr>
                            <th colspan="2" style="text-align: center; vertical-align: middle;">Código de {{ $tabla }}</th>
                            <th colspan="2" style="text-align: center; vertical-align: middle;">Nombre de {{ $tabla }}</th>
                            <th rowspan="2" style="text-align: center; vertical-align: middle;">Estado de Campos</th>
                            <th rowspan="2" style="text-align: center; vertical-align: middle;">Estado de Geometrías</th>
                            <th rowspan="2" style="text-align: center; vertical-align: middle;">Cantidad de Errores</th>
                        </tr>

                        <tr>
                            <th scope="col" style="text-align: center; vertical-align: middle;">En 
                            @if ($geoservicio)
                                {{ $geoservicio->nombre }} 
                            @else
                                - 
                            @endif
                            ("{{ $cod }}")</th>
                            <th scope="col" style="text-align: center; vertical-align: middle;">En Base de Datos</th>
                            
                            <th scope="col" style="text-align: center; vertical-align: middle;">En 
                            @if ($geoservicio)
                                {{ $geoservicio->nombre }} 
                            @else
                                - 
                            @endif
                            ("{{ $nom }}")</th>
                            <th scope="col" style="text-align: center; vertical-align: middle;">En Base de Datos</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($resultados as $resultado)
                            @if ($tipo_informe == "resultado")
                                <tr class="@if($resultado['estado'] == 'No hay coincidencias') table-danger @elseif($resultado['estado'] == 'Diferencia en el código' || $resultado['estado'] == 'Diferencia en el nombre') table-warning @endif">
                                    <td style="text-align: center; vertical-align: middle;">
                                        {{ $resultado['feature']['properties'][$cod] }}
                                    </td>
                                    <td style="text-align: center; vertical-align: middle;">
                                        {{ $resultado['provincia'] ? $resultado['provincia']['codigo'] : '-' }}
                                    </td>
                                    <td style="text-align: center; vertical-align: middle;" >
                                        {{ $resultado['feature']['properties'][$nom] }}
                                        @if ($resultado['estado'] == 'Diferencia en el nombre')
                                            <br>
                                            (UTF-8: {{ mb_convert_encoding($resultado['feature']['properties'][$nom], 'UTF-8', 'ISO-8859-1') }})
                                        @endif
                                    </td>
                                    <td style="text-align: center; vertical-align: middle;">
                                        {{ $resultado['provincia'] ? $resultado['provincia']['nombre'] : '-' }}
                                        @if ($resultado['provincia'] and $resultado['estado'] == 'Diferencia en el nombre')
                                            <br>
                                            (UTF-8: {{ mb_convert_encoding($resultado['provincia']['nombre'], 'UTF-8', 'ISO-8859-1') }})
                                        @endif
                                    </td>
                                    <td style="text-align: center; vertical-align: middle;">
                                        {{ $resultado['estado'] }}
                                    </td>
                                    <td style="text-align: center; vertical-align: middle;" 
                                    @if ($resultado['provincia'])
                                        id="estado_geom_{{$resultado['provincia']['codigo']}}"
                                    @endif
                                    >
                                    @if ($resultado['estado_geom'] == 'No hay geometría cargada en el geoservicio')
                                        Base de Datos: <i style="color:green" class="bi bi-check"></i><br>
                                        Geoservicio: <i style="color:red" class="bi bi-x"></i>
                                    @elseif ($resultado['estado_geom'] == 'No hay geometría cargada en la BD')
                                        Base de Datos: <i style="color:red" class="bi bi-x"></i><br>
                                        Geoservicio: <i style="color:green" class="bi bi-check"></i><br>
                                        @can('Importar Geometrias')
                                        <button type="button" class="btn btn-success btn-sm importar-btn" data-cod-prov="{{$resultado['provincia']['codigo']}}">Importar</button>
                                        @endcan
                                    @elseif ($resultado['estado_geom'] == 'No hay geometrías cargadas')
                                        Base de Datos: <i style="color:red" class="bi bi-x"></i><br>
                                        Geoservicio: <i style="color:red" class="bi bi-x"></i>
                                    @elseif ($resultado['estado_geom'] == '-')
                                        -
                                    @else
                                        Base de Datos: <i style="color:green" class="bi bi-check"></i><br>
                                        Geoservicio: <i style="color:green" class="bi bi-check"></i><br>
                                        Diferencia: <i>{{ $resultado['estado_geom'] }}</i>
                                    @endif
                                    </td>
                                    <td style="text-align: center; vertical-align: middle;">
                                        {{ $resultado['errores']}}
                                    </td>
                                </tr>
                            @elseif ($tipo_informe == "informe")
                                <tr class="@if($resultado->estado == 'No hay coincidencias') table-danger @elseif($resultado->estado == 'Diferencia en el código' || $resultado->estado == 'Diferencia en el nombre') table-warning @endif">
                                    <td style="text-align: center; vertical-align: middle;">
                                        {{ $resultado->cod }}
                                    </td>
                                    <td style="text-align: center; vertical-align: middle;">
                                        {{ $resultado->provincia_id ? $resultado->provincia->codigo : '-' }}
                                    </td>
                                    <td style="text-align: center; vertical-align: middle;" >
                                        {{ $resultado->nom }}
                                        @if ($resultado->estado == 'Diferencia en el nombre')
                                            <br>
                                            (UTF-8: {{ mb_convert_encoding($resultado->nom, 'UTF-8', 'ISO-8859-1') }})
                                        @endif
                                    </td>
                                    <td style="text-align: center; vertical-align: middle;">
                                        {{ $resultado->provincia_id ? $resultado->provincia->nombre : '-' }}
                                        @if ($resultado->provincia_id and $resultado->estado == 'Diferencia en el nombre')
                                            <br>
                                            (UTF-8: {{ mb_convert_encoding($resultado->provincia->nombre, 'UTF-8', 'ISO-8859-1') }})
                                        @endif
                                    </td>
                                    <td style="text-align: center; vertical-align: middle;">
                                        {{ $resultado->estado }}
                                    </td>
                                    <td style="text-align: center; vertical-align: middle;">
                                    @if ($resultado->estado_geom == 'No hay geometría cargada en el geoservicio')
                                        Base de Datos: <i style="color:green" class="bi bi-check"></i><br>
                                        Geoservicio: <i style="color:red" class="bi bi-x"></i>
                                    @elseif ($resultado->estado_geom == 'No hay geometría cargada en la BD')
                                        Base de Datos: <i style="color:red" class="bi bi-x"></i><br>
                                        Geoservicio: <i style="color:green" class="bi bi-check"></i><br>
                                    @elseif ($resultado->estado_geom == 'No hay geometrías cargadas')
                                        Base de Datos: <i style="color:red" class="bi bi-x"></i><br>
                                        Geoservicio: <i style="color:red" class="bi bi-x"></i>
                                    @elseif ($resultado->estado_geom == '-')
                                        -
                                    @else
                                        Base de Datos: <i style="color:green" class="bi bi-check"></i><br>
                                        Geoservicio: <i style="color:green" class="bi bi-check"></i><br>
                                        Diferencia: <i>{{ $resultado->estado_geom }}</i>
                                    @endif
                                    </td>
                                    <td style="text-align: center; vertical-align: middle;">
                                        {{ $resultado->errores}}
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
@section('footer_scripts')
<script>
    $('#tabla-resultado').DataTable({
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

    $(document).ready(function() {
        var showConfirmation = true;
        $('#tabla-resultado').on('click', '.importar-btn', function() {
            var cod_provincia = $(this).data('cod-prov');
            var $rows = $('button[data-cod-prov="' + cod_provincia + '"]').closest('tr'); //no funciona para las filas paginadas pero no importa

            function importaGeometriaAjax() {
                $.ajax({
                    url: '{{ route("compare.importarGeom") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        cod_provincia: cod_provincia,
                    },
                    success: function(response) {
                        var alertClass = (response.statusCode == 200) ? 'alert-success' : 'alert-danger';
                        var alertHtml = '<div class="alert ' + alertClass + ' alert-dismissible" role="alert">' +
                                            '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                                            response.message +
                                        '</div>';
                        $('#alert-container').html(alertHtml);
                        if (response.statusCode == 200){
                            $rows.each(function() {
                                var $row = $(this);
                                $row.find('.importar-btn').hide();
                                var cod_provincia = $row.data('cod-prov');
                                var tdId = '#estado_geom_' + cod_provincia;
                                $(tdId).html(
                                    'Base de Datos: <i style="color:green" class="bi bi-check"></i><br>' +
                                    'Geoservicio: <i style="color:green" class="bi bi-check"></i><br>' +
                                    'Diferencia: <i>' + response.estado_geom + '</i>'
                                );
                                // resalto la fila
                                $row.addClass('highlight');
                                setTimeout(function() {
                                    $row.removeClass('highlight');
                                }, 2000);
                            });
                        } else if (response.statusCode == 403) {
                            $rows.each(function() {
                                var $row = $(this);
                                // resalto la fila
                                $row.addClass('highlight-error');
                                setTimeout(function() {
                                    $row.removeClass('highlight-error');
                                }, 2000);
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                        alert('Error al importar la geometría.');
                    }
                });
            }

            if (showConfirmation) {
                $('#confirmationModal').modal('show');

                $('#confirmButton').off('click').on('click', function() {
                    if ($('#dontShowAgainCheckbox').is(':checked')) {
                        showConfirmation = false;
                    }
                    $('#confirmationModal').modal('hide');
                    importaGeometriaAjax();
                });
            } else {
                importaGeometriaAjax();
            }
        });
    });

    $(document).ready(function() {
        $('#btn-guardar').on('click', function() {
            var button = $(this);
            var spinner = $('#spinner');
            var btnText = $('#btn-text');
            button.prop('disabled', true);
            spinner.show();
            btnText.hide();

            $.ajax({
                url: '{{ route("compare.storeInforme") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                },
                success: function(response) {
                    spinner.hide();
                    btnText.text('Guardado');
                    btnText.show();
                },
                error: function(xhr, status, error) {
                    console.error(error);
                    alert('Error al crear el informe.');
                    spinner.hide();
                    btnText.show();
                    button.prop('disabled', false);
                }
            });
        });
    });

</script>
@endsection
