@extends('layouts.app')

@section('content_main')
<style>
    .top-container {
        display: flex;
    }

    #info {
        flex: 3; /* Toma el 75% del ancho */
    }

    #store {
        justify-content: right; /* Alinea el texto a la derecha dentro del div */
        align-items: top; /* Centra verticalmente el texto */
    }

</style>
<div class="container">
    <div class="row justify-content-center"> 
        <div class="card" style="width: 120%">
            <div class="card-header">
                Resultado de la comparación de la capa <b>{{ $capa }}</b> con la tabla de <b>{{ $tabla }}s</b>
            </div>
            <div class="card-body">
                <div class="top-container">
                    <div id="info">
                        Informe realizado el <b>{{ $datetime->format('d-m-Y H:i:s') }}</b> por <b>{{ $usuario->name }}</b> <br>
                        Operativo: {{ $operativo }} <br>
                        Total de {{ strtolower($tabla) }}s con errores: {{ $elementos_erroneos }} <br>
                        Total de errores de validación: {{ $total_errores }} <br>
                    </div>
                    <div id="store"><button type="button" id="btn-guardar" class="btn btn-primary mr-2"
                        data-capa="{{ $capa }}" 
                        data-tabla="{{ $tabla }}" 
                        data-elementos_erroneos="{{ $elementos_erroneos }}" 
                        data-total_errores="{{ $total_errores }}" 
                        data-cod="{{ $cod }}" 
                        data-nom="{{ $nom }}" 
                        data-operativo-id="-" 
                        data-datetime="{{ $datetime }}" 
                        data-user-id="{{ $usuario->id }}" 
                        data-resultados="{{ json_encode($resultados) }}">
                        <span id="spinner" style="display: none;">
                            <div class="spinner-border spinner-border-sm" role="status">
                                <span class="sr-only">Cargando...</span>
                            </div>
                        </span>
                        <span id="btn-text">Guardar informe</span>
                        <i class="bi bi-floppy ml-2"></i></button></div>
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
                            <th scope="col" style="text-align: center; vertical-align: middle;">En Geonode ("{{ $cod }}")</th>
                            <th scope="col" style="text-align: center; vertical-align: middle;">En Base de Datos</th>
                            
                            <th scope="col" style="text-align: center; vertical-align: middle;">En Geonode ("{{ $nom }}")</th>
                            <th scope="col" style="text-align: center; vertical-align: middle;">En Base de Datos</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($resultados as $resultado)
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
                                        (UTF-8: {{ utf8_encode($resultado['feature']['properties'][$nom]) }})
                                    @endif
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    {{ $resultado['provincia'] ? $resultado['provincia']['nombre'] : '-' }}
                                    @if ($resultado['provincia'] and $resultado['estado'] == 'Diferencia en el nombre')
                                        <br>
                                        (UTF-8: {{ utf8_encode($resultado['provincia']['nombre']) }})
                                    @endif
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    {{ $resultado['estado'] }}
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                @if ($resultado['estado_geom'] == 'No hay geometría cargada en el geoservicio')
                                    Base de Datos: <i style="color:green" class="bi bi-check"></i><br>
                                    Geoservicio: <i style="color:red" class="bi bi-x"></i>
                                @elseif ($resultado['estado_geom'] == 'No hay geometría cargada en la BD')
                                    Base de Datos: <i style="color:red" class="bi bi-x"></i><br>
                                    Geoservicio: <i style="color:green" class="bi bi-check"></i><br>
                                    <button type="button" class="btn btn-success btn-sm importar-btn" data-cod-prov="{{$resultado['provincia']['codigo']}}" data-geom-feature="{{json_encode($geometrias[$resultado['feature']['id']])}}">Importar</button>
                                @elseif ($resultado['estado_geom'] == 'No hay geometrías cargadas')
                                    Base de Datos: <i style="color:red" class="bi bi-x"></i><br>
                                    Geoservicio: <i style="color:red" class="bi bi-x"></i>
                                @elseif ($resultado['estado_geom'] == '-')
                                    -
                                @else
                                    Base de Datos: <i style="color:green" class="bi bi-check"></i><br>
                                    Geoservicio: <i style="color:green" class="bi bi-check"></i><br>
                                    Diferencia: {{ $resultado['estado_geom'] }} KM2
                                @endif
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    {{ $resultado['errores']}}
                                </td>
                            </tr>
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
        $('.importar-btn').on('click', function() {
            var cod_provincia = $(this).data('cod-prov');
            var geomFeature = JSON.stringify($(this).data('geom-feature'));
            var $button = $(this);

            console.log(cod_provincia);
            console.log(geomFeature);

            $.ajax({
                url: '{{ route("compare.importarGeom") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    cod_provincia: cod_provincia,
                    geom_feature: geomFeature
                },
                success: function(response) {
                    if (response.statusCode == 200){
                        alert('Geometría importada correctamente. ID de nueva geometría: '+response.id_new_geom);
                        $button.hide();
                    } else {
                        alert('Error al importar la geometría.');
                    }
                },
                error: function(xhr, status, error) {
                    console.error(error);
                    alert('Error al importar la geometría.');
                }
            });
        });
    });

    $(document).ready(function() {
        $('#btn-guardar').on('click', function() {
            var button = $(this);
            var spinner = $('#spinner');
            var btnText = $('#btn-text');
            spinner.show();
            btnText.hide();
            var data = {
                _token: '{{ csrf_token() }}',
                capa: button.data('capa'),
                tabla: button.data('tabla'),
                elementos_erroneos: button.data('elementos_erroneos'),
                total_errores: button.data('total_errores'),
                cod: button.data('cod'),
                nom: button.data('nom'),
                //operativo_id: button.data('operativo'),
                datetime: button.data('datetime'),
                user_id: button.data('user-id'),
                resultados: button.data('resultados')
            };

            $.ajax({
                url: '{{ route("compare.storeInforme") }}',
                method: 'POST',
                data: data,
                success: function(response) {
                    button.prop('disabled', true);
                    spinner.hide();
                    btnText.text('Guardado');
                    btnText.show();
                },
                error: function(xhr, status, error) {
                    console.error(error);
                    alert('Error al guardar el informe.');
                    spinner.hide();
                    btnText.show();
                }
            });
        });
    });
</script>
@endsection
