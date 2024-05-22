@extends('layouts.app')

@section('content_main')
<div class="container justify-content-center" style="width: 40%">
    <div class="row justify-content-center">   
        <h3>Seleccione el Geoservicio a utilizar para la validación</h3>
    </div>
    <br>
    <div class="row justify-content-center">
        <div class="card" style="width: 30rem;">
            <div class="card-body">
                <form action="{{route(compare.setGeoservicio)}}" method="POST">
                    @csrf
                    <div class="row justify-content-center">
                        <div class="col">  
                            <label for="geoservicio">Geoservicio:</label>
                            <select name="geoservicio" id="geoservicio" class="form-control">
                                @foreach ($geoservicios as $geoservicio)
                                    <option value="{{ $geoservicio }}">{{ $geoservicio->nombre }} ({{ $geoservicio->descripcion }})</option>
                                @endforeach
                            </select>
                            <br>
                            <div class="row justify-content-center">
                            <button type="submit" class="btn btn-primary mr-1">Seleccionar</button>
                            <button type="button" class="btn btn-primary mr-1">Nuevo Geoservicio</button>
                            <a type="button" href="{{route('compare.menu')}}" class="btn btn-secondary">Volver</a>
                            </div>
                        </div>  
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
@section('footer_scripts')
@endsection
