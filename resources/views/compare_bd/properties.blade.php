@extends('layouts.app')

@section('content_main')
<div class="container justify-content-center" style="width: 50%">
    <div id="alert-container">
        <div class="alert alert-warning justify-content-center alert-dismissable" role="alert">
            <b>ATENCIÓN:</b> Función en desarrollo. La comparación de la capa se realizará contra la tabla Provincia.
        </div>
    </div>
    <div class="row justify-content-center">   
        <h4>Validación de Base de Datos con capas de {{$geoservicio->nombre}}</h4>
    </div>
    <br>
    <div class="row justify-content-center">
        <div class="card" style="width: 50rem;">
            <div class="card-header">
                Seleccionar campos a comparar para la capa <b>{{explode(':', $capa)[1] }}</b>
            </div>
            <div class="card-body">
                <form action="{{ route('compare.validar', ['geoservicio' => json_encode($geoservicio), 'capa' => $capa]) }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-lg-6">
                            <label for="codigo">Código:</label>
                            <select name="codigo" id="codigo" class="form-control">
                                @foreach ($atributos as $atributo)
                                    <option value="{{ $atributo['name'] }}">{{ $atributo['name'] }} (tipo: {{explode(':', $atributo['type'])[1]}})</option>
                                @endforeach
                            </select>
                            <i>(se comparará con el campo "codigo" de Provincia)</i>
                        </div>
                        <div class="col-lg-6">
                            <label for="nombre">Nombre:</label>
                            <select name="nombre" id="nombre" class="form-control">
                                @foreach ($atributos as $atributo)
                                    <option value="{{ $atributo['name'] }}">{{ $atributo['name'] }} (tipo: {{explode(':', $atributo['type'])[1]}})</option>
                                @endforeach
                            </select>
                            <i>(se comparará con el campo "nombre" de Provincia)</i>
                        </div>
                    </div>
                    <br>
                    <button type="submit" class="btn btn-primary">Comparar</button>
                    <a type="button" href="{{route('compare.capas', ['geoservicio' => json_encode($geoservicio)])}}" class="btn btn-secondary ml-2">Volver</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
@section('footer_scripts')
@endsection
