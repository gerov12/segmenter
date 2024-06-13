@extends('layouts.app')

@section('content_main')
<style>
    .flex-container {
        display: flex;
        justify-content: center;
        gap: 5px;
    }

    .flex-container a {
        margin: 0;
    }
</style>
<div style="width:85%; margin: 0 auto;">
    <div>
        <h4><a href="{{route('compare.menu')}}" class="badge badge-pill badge-primary">← Volver</a></h4>
    </div>
    <div class="row justify-content-center">  
        <div class="card w-100">
            <div class="card-header">
                Listado de informes de validación de la Base de Datos contra Geoservicios
            </div>
            <div class="card-body">
                <table class="table table-responsive table-bordered" id="tabla-informes">
                    <thead>
                        <tr>
                            <th>Geoservicio</th>
                            <th>Capa</th>
                            <th>Tabla</th>
                            <th>Operativo</th>
                            <th>Elementos encontrados</th>
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
                                    {{ $informe->geoservicio->nombre }}<br>
                                    @if ($informe->geoservicio->descripcion)
                                        ({{ $informe->geoservicio->descripcion }})<br>
                                    @endif
                                    <i>{{ $informe->geoservicio->url }}</i>
                                @else
                                    {{ $informe->geoservicio_nombre }} <span title="Conexión rápida" class="badge badge-pill badge-success"><i class="bi bi-lightning-charge"></i></span><br>
                                    @if ($informe->geoservicio_descripcion)
                                        ({{ $informe->geoservicio_descripcion }})<br>
                                    @endif
                                    <i>{{ $informe->geoservicio_url }}</i>
                                @endif
                                </td>
                                <td>{{explode(':', $informe->capa)[1] }}</td>
                                <td>{{$informe->tabla}}</td>
                                <td>{{$informe->operativo}}</td>
                                <td>{{$informe->provincias()->count()}}</td>
                                <td>{{$informe->elementos_erroneos}}</td>
                                <td>{{$informe->total_errores}}</td>
                                <td>{{$informe->user->name}}</td>
                                <td>{{$informe->datetime->format('d-m-Y H:i:s')}}</td>
                                <th>
                                <div id="botones" class="flex-container">
                                    <a type="button" href="{{ route('compare.verInforme',$informe->id) }}" class="btn-sm btn-primary">Ver</a>
                                    @can('Generar Informes')
                                    <a type="button" href="{{ route('compare.repetirInforme',$informe->id) }}" class="btn-sm btn-success">Repetir</a>
                                    @endcan
                                </div>  
                                </th>
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

    document.addEventListener("DOMContentLoaded", function() {
        var table = document.getElementById('tabla-informes');
        var tbody = table.querySelector('tbody');
        var rows = tbody.getElementsByTagName('tr');
        
        if (rows.length === 1 && rows[0].textContent.trim() === "Ningún dato disponible en esta tabla =(") {
            table.style.display = 'table';
        } else {
            table.style.display = 'block';
        }
    });

</script>
@endsection
