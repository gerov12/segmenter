@extends('layouts.app')

@section('content_main')
<div class="container justify-content-center" style="width: 40%">
    <div id="alert-container">
        <div class="alert alert-warning justify-content-center alert-dismissable" role="alert">
            <b>ATENCIÓN:</b> Función en desarrollo. La comparación de la capa se realizará contra la tabla Provincia.
        </div>
        @if(Session::has('message'))
            <div class="alert alert-success alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            {{Session::get('message')}}
            </div>
        @endif
    </div>
    <div class="row justify-content-center">   
        <h4>Validación de Base de Datos con capas de {{$geoservicio->nombre}}</h4>
    </div>
    <br>
    <div class="row justify-content-center">
        <div class="card" style="width: 30rem;">
            <div class="card-header">
                Seleccionar capa para la comparación
            </div>
            <div class="card-body">
                <form action="{{ route('compare.atributos', ['geoservicio' => json_encode($geoservicio)]) }}" method="POST">
                    @csrf
                    <div class="row justify-content-center">
                        <div class="col-lg-15">  
                            <label for="capa">Capa:</label>
                            <select name="capa" id="capa" class="form-control">
                                @foreach ($capas as $capa)
                                    <option value="{{ $capa['Name'] }}">{{ $capa['Title'] }} ({{ $capa['Name'] }})</option>
                                @endforeach
                            </select>
                            <br>
                        </div>
                        <button type="submit" class="btn btn-primary">Seleccionar</button>
                        <a type="button" href="{{route('compare.geoservicios')}}" class="btn btn-secondary ml-2">Volver</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
@section('footer_scripts')
@endsection
