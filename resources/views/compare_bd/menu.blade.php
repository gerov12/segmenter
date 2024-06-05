@extends('layouts.app')

@section('content_main')
<div class="container justify-content-center" style="width: 40%">
    <div class="row justify-content-center">   
        <h4>Validación de Base de Datos con capas de Geoservicios</h4>
    </div>
    <br>
    <div class="row justify-content-center">
        @can('Generar Informes')
        <a type="button" href="{{ route('compare.geoservicios') }}" class="btn btn-outline-primary btn-lg btn-block" type="button">Nueva comparación</a>
        @endcan
        @can('Ver Informes') 
        <a type="button" href="{{ route('compare.informes') }}"class="btn btn-outline-secondary btn-lg btn-block" type="button">Ver informes anteriores</a>
        @endcan
    </div>
</div>
@endsection
@section('footer_scripts')
@endsection
