@extends('layouts.app')

@section('content_main')
<div class="container">
    <div class="row justify-content-center"> 
        <div class="card" style="width: 120%">
            <div class="card-header">
                Resultado de la comparación de la capa <b>{{ $capa }}</b> con la tabla de <b>{{ $tabla }}s</b>
            </div>
            <div class="card-body">
                <div id="info">
                    Informe realizado el <b>{{ $datetime }}</b> por <b>{{ $usuario }}</b> <br>
                    Operativo: {{ $operativo }} <br>
                    Total de {{ strtolower($tabla) }}s con errores: {{ $elementos_erroneos }} <br>
                    Total de errores de validación: {{ $total_errores }} <br>
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
                                    <button type="button" class="btn btn-success btn-sm importar-btn" data-cod-prov="{{$resultado['provincia']['codigo']}}" data-geom-feature="{{$resultado['geom_feature']}}">Importar</button>
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
</script>
@endsection
