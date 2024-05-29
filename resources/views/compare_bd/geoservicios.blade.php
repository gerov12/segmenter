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
        <h3>Seleccione el Geoservicio a utilizar para la validación</h3>
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
                                        <option value="{{ $geoservicio->id }}">
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
                            <button type="button" disabled class="btn btn-info mr-1" style="color:white">Editar</button>
                            <button type="button" class="btn btn-success mr-1" data-toggle="modal" data-target="#nuevoGeoservicioModal">Nuevo Geoservicio</button>
                            <a type="button" href="{{route('compare.menu')}}" class="btn btn-secondary">Volver</a>
                            </div>
                        </div>  
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
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
                        @if ($errors->has('nombre'))
                            <div class="text-danger">{{ $errors->first('nombre') }}</div>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="descripcion">Descripción (opcional):</label>
                        <input type="text" class="form-control" id="descripcion" name="descripcion">
                        @if ($errors->has('descripcion'))
                            <div class="text-danger">{{ $errors->first('descripcion') }}</div>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="url">URL:</label>
                        <input type="url" class="form-control" id="url" name="url" required>
                        @if ($errors->has('url'))
                            <div class="text-danger">{{ $errors->first('url') }}</div>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="tipo">Tipo:</label>
                        <select class="form-control" id="tipo" name="tipo" required>
                            <option value="wfs">WFS</option>
                        </select>
                        @if ($errors->has('tipo'))
                            <div class="text-danger">{{ $errors->first('tipo') }}</div>
                        @endif
                    </div>
                </form>
            </div>
            <div class="modal-footer d-flex justify-content-center">
                <button type="button" class="btn btn-success" onclick="submitForm('geoservicios/initialize')">Conexión rápida <i class="bi bi-lightning-charge"></i></button>
                <div class="btn-group">
                    <button type="button" class="btn btn-primary" onclick="submitForm('geoservicios/store-and-connect')">Guardar y seleccionar</button>
                    <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <div class="dropdown-menu">
                        <button class="dropdown-item" type="button" onclick="submitForm('geoservicios/store')">Solo guardar</button>
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
    @if ($errors->any())
        $(document).ready(function() {
            $('#nuevoGeoservicioModal').modal('show');
        });
    @endif
    function submitForm(action) {
        const form = document.getElementById('nuevoGeoservicioForm');
        form.action = action;
        form.submit();
    }
</script>
@endsection
