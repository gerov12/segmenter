@extends('layouts.app')

@section('content_main')
<div class="container">
    <div style="display: flex; align-items: center; justify-content: center;">
        <div style="width: 90rem; display: flex; align-items: center;"> 
        <h4><a href="{{route('compare.menu')}}" class="badge badge-pill badge-primary">← Volver</a></h4>
        </div>
    </div>
    <div class="row justify-content-center"> 
        <div class="card" style="width: 120%">
            <div class="card-header">
                Listado de informes de validación de la Base de Datos contra Geoservicios
            </div>
            <div class="card-body">
                <table class="table table-bordered" id="tabla-informes" style="width:100%">
                    <thead>
                        <tr>
                            <th>Geoservicio</th>
                            <th>Capa</th>
                            <th>Tabla</th>
                            <th>Operativo</th>
                            <th>Elementos con error</th>
                            <th>Errores totales</th>
                            <th>Usuario</th>
                            <th>Realizado el</th>
                            <th>*</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($informes as $informe)
                            <tr>
                                <td>
                                @if ($informe->geoservicio_id)
                                    {{ $informe->geoservicio->nombre }}
                                @else
                                    -
                                @endif
                                </td>
                                <td>{{explode(':', $informe->capa)[1] }}</td>
                                <td>{{$informe->tabla}}</td>
                                <td>{{$informe->operativo}}</td>
                                <td>{{$informe->elementos_erroneos}}</td>
                                <td>{{$informe->total_errores}}</td>
                                <td>{{$informe->user->name}}</td>
                                <td>{{$informe->datetime->format('d-m-Y H:i:s')}}</td>
                                <th><a type="button" href="{{ route('compare.verInforme',$informe->id) }}" class="btn btn-primary">Ver</a></th>
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
    $('#tabla-informes').DataTable({
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
@endsection
