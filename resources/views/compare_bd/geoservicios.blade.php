@extends('layouts.app')

@section('content_main')
<div class="container justify-content-center" style="width: 40%">
    <div id="alert-container">
      @if(Session::has('error'))
        <div class="alert alert-danger alert-dismissible" role="alert">
          <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          {{Session::get('error')}}
        </div>
      @endif
    </div>
    <div class="row justify-content-center">   
        <h4>Seleccione el Geoservicio a utilizar para la validación</h4>
    </div>
    <br>
    <div class="row justify-content-center">
        <div class="card" style="width: 30rem;">
            <div class="card-body">
                <form action="{{route('compare.initGeoservicio')}}" method="POST">
                    @csrf
                    <div class="row justify-content-center">
                        <div class="col">  
                            <label for="geoservicio_id">Geoservicio:</label>
                            <select name="geoservicio_id" id="geoservicio_id" class="form-control">
                                @if ($geoservicios->isEmpty())
                                    <option value="" disabled selected>No hay geoservicios cargados</option>
                                @else
                                    @foreach ($geoservicios as $geoservicio)
                                        <option value="{{ $geoservicio->id }}" data-geoservicio="{{ json_encode($geoservicio) }}">
                                            {{ $geoservicio->nombre }} 
                                            @if ($geoservicio->descripcion)
                                                <i>({{ $geoservicio->descripcion }}) </i>
                                            @endif
                                            <i>[{{ $geoservicio->url }}]</i>
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            <br>
                            <div class="row justify-content-center">
                            <button type="submit" class="btn btn-primary mr-1">Seleccionar</button>
                            @can('Administrar Geoservicios')
                            <button type="button" id="editarGeoservicioButton" class="btn btn-primary mr-1" @if($geoservicios->isEmpty()) disabled @endif>Editar</button>
                            <button type="button" class="btn btn-success mr-1" data-toggle="modal" data-target="#nuevoGeoservicioModal">Nuevo Geoservicio</button>
                            @endcan
                            <a type="button" href="{{route('compare.menu')}}" class="btn btn-secondary">Volver</a>
                            </div>
                        </div>  
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal nuevo geoservicio -->
<div class="modal fade" id="nuevoGeoservicioModal" tabindex="-1" role="dialog" aria-labelledby="nuevoGeoservicioModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="nuevoGeoservicioModalLabel">Nuevo Geoservicio</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="nuevoGeoservicioForm" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="nombre">Nombre:</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                        @if ($errors->new->has('nombre'))
                            <div class="text-danger">{{ $errors->new->first('nombre') }}</div>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="descripcion">Descripción (opcional):</label>
                        <input type="text" class="form-control" id="descripcion" name="descripcion">
                        @if ($errors->new->has('descripcion'))
                            <div class="text-danger">{{ $errors->new->first('descripcion') }}</div>
                        @endif
                    </div>
                    <div class="form-group">
                    <i class="bi bi-info-circle" id="info-popover" type="button" data-container="body" data-toggle="popover" data-placement="right"></i>
                    <label for="url">URL:</label>
                        <input type="url" class="form-control" id="url" name="url" required>
                        @if ($errors->new->has('url'))
                            <div class="text-danger">{{ $errors->new->first('url') }}</div>
                        @endif
                    </div>
                </form>
            </div>
            <div class="modal-footer d-flex justify-content-center">
                <button type="button" class="btn btn-success" onclick="submitForm('{{route('compare.initGeoservicio')}}','new')">Conexión rápida <i class="bi bi-lightning-charge"></i></button>
                <div class="btn-group">
                    <button type="button" class="btn btn-primary" onclick="submitForm('{{route('compare.storeGeoservicioAndConnect')}}','new')">Guardar y seleccionar</button>
                    <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <div class="dropdown-menu">
                        <button class="dropdown-item" type="button" onclick="submitForm('{{route('compare.storeGeoservicio')}}','new')">Solo guardar</button>
                    </div>
                </div>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal editar geoservicio -->
<div class="modal fade" id="editarGeoservicioModal" tabindex="-1" role="dialog" aria-labelledby="editarGeoservicioModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editarGeoservicioModalLabel">Editar Geoservicio</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editarGeoservicioForm" method="POST">
                    @csrf
                    <input type="hidden" id="edit-geoservicio-id" name="geoservicio_id">
                    <div class="form-group">
                        <label for="edit-nombre">Nombre:</label>
                        <input type="text" class="form-control" id="edit-nombre" name="nombre" required>
                        @if ($errors->edit->has('nombre'))
                            <div class="text-danger">{{ $errors->edit->first('nombre') }}</div>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="edit-descripcion">Descripción (opcional):</label>
                        <input type="text" class="form-control" id="edit-descripcion" name="descripcion">
                        @if ($errors->edit->has('descripcion'))
                            <div class="text-danger">{{ $errors->edit->first('descripcion') }}</div>
                        @endif
                    </div>
                    <div class="form-group">
                    <i class="bi bi-info-circle" id="info-popover" type="button" data-container="body" data-toggle="popover" data-placement="right"></i>
                    <label for="edit-url">URL:</label>
                        <input type="url" class="form-control" id="edit-url" name="url" required>
                        @if ($errors->edit->has('url'))
                            <div class="text-danger">{{ $errors->edit->first('url') }}</div>
                        @endif
                    </div>
                </form>
            </div>
            <div class="modal-footer d-flex justify-content-center">
                <button type="button" class="btn btn-danger" onclick="submitForm('{{route('compare.deleteGeoservicio')}}','delete')">Eliminar</button>
                <div class="btn-group">
                    <button type="button" class="btn btn-primary" onclick="submitForm('{{route('compare.storeGeoservicio')}}','edit')">Guardar</button>
                    <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <div class="dropdown-menu">
                        <button class="dropdown-item" type="button" onclick="submitForm('{{route('compare.storeGeoservicioAndConnect')}}','edit')">Guardar y seleccionar</button>
                    </div>
                </div>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>
@endsection
@section('footer_scripts')
<script>
    $(document).ready(function () {
        $('#info-popover').popover({
            html: true,
            title: "URLs válidas",
            content: `
                <p>Ejemplos de formatos correctos</p>
                <ul>
                    <li>https://geoservicio.ejemplo/geoserver</li>
                    <li>https://geoservicio.ejemplo/geoserver/</li>
                    <li>https://geoservicio.ejemplo/geoserver/wfs</li>
                    <li>https://geoservicio.ejemplo/geoserver/wfs?version=x.x.x</li>
                    <li>https://geoservicio.ejemplo/geoserver/wfs?service=wfs</li>
                </ul>
                <p>Si no se especifica nada después de <code>geoserver/</code>, se añadirá <code>wfs</code> por defecto.</p>
                <p>Los parámetros <code>service</code> y <code>version</code> pueden declararse simultaneamente. Cualquier otro parámetro será ignorado.</p>
            `
        });

        $('[data-toggle="popover"]').popover();

        // cerrar el popover cuando se hace clic fuera de él
        $(document).on('click', function (e) {
            if (!$(e.target).closest('#info-popover').length) {
                $('#info-popover').popover('hide');
            }
        });

        // evitar que el popover se cierre cuando se hace clic dentro de él
        $('#info-popover').on('shown.bs.popover', function () {
            $('.popover').on('click', function (e) {
                e.stopPropagation();
            });
        });

        function openEditModal(geoservicio) {
            if (geoservicio) {
                $('#edit-geoservicio-id').val(geoservicio.id);
                $('#edit-nombre').val(geoservicio.nombre);
                $('#edit-descripcion').val(geoservicio.descripcion);
                $('#edit-url').val(geoservicio.url);
                $('#editarGeoservicioModal').modal('show');
            }
        }

        $('#editarGeoservicioButton').on('click', function() {
            var selectedGeoservicio = $('#geoservicio_id').find('option:selected').data('geoservicio');
            openEditModal(selectedGeoservicio);
        });

        @if ($errors->hasBag('edit'))
            $(document).ready(function() {
                var geoservicioFromSession = {!! json_encode(session('geoservicio')) !!};
                if (geoservicioFromSession) {
                    openEditModal(geoservicioFromSession);
                } else {
                    var selectedGeoservicio = $('#geoservicio_id').find('option:selected').data('geoservicio');
                    openEditModal(selectedGeoservicio);
                }
            });
        @elseif ($errors->hasBag('new'))
            $('#nuevoGeoservicioModal').modal('show');
        @endif
    });

    function submitForm(action, type) {
        var form;
        if (type === "new") {
            form = document.getElementById('nuevoGeoservicioForm');
        } else if (type === "edit" || type === "delete") {
            form = document.getElementById('editarGeoservicioForm');
        }

        if (type === "delete") {
            var confirmed = confirm('¿Estás seguro de que deseas eliminar este geoservicio?. En caso de haber sido consultado para algun informe, pasará a figurar como "Conexión rápida"');
            if (!confirmed) {
                return; // no hago nada si no confirmo
            }
        }

        form.action = action;
        form.submit();
    }
</script>
@endsection
