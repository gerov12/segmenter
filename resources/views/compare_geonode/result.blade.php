@extends('layouts.app')

@section('content_main')
<div class="container">
    <div class="row justify-content-center">
        <div class="card" style="width: 70rem;">
            <div class="card-header">
                Resultado de la comparación de la capa <b>{{ $capa }}</b> con la tabla de <b>{{ $tabla }}s</b>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th colspan="2" style="text-align: center; vertical-align: middle;">Código de {{ $tabla }}</th>
                            <th colspan="2" style="text-align: center; vertical-align: middle;">Nombre de {{ $tabla }}</th>
                            <th rowspan="2" style="text-align: center; vertical-align: middle;">Estado</th>
                            <th rowspan="2" style="text-align: center; vertical-align: middle;">Diferencia en geometría (KM2)</th>
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
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    {{ $resultado['provincia'] ? $resultado['provincia']['nombre'] : '-' }}
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    {{ $resultado['estado'] }}
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    -
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <tfoot>
                    Total de provincias con errores: {{ $resultado['provincias_erroneas'] }} <br>
                    Total de errores de validación: {{ $resultado['total_errores'] }}
                </tfoot>
            </div>
        </div>
    </div>
</div>
@endsection
@section('footer_scripts')
@endsection
