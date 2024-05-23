@extends('layouts.app')

@section('content_main')
<div class="container justify-content-center" style="width: 40%">
    <div class="row justify-content-center">   
        <h3>Validación de Base de Datos con capas de Geonode</h3>
    </div>
    <br>
    <div class="row justify-content-center">
        <a type="button" href="{{ route('compare.geoservicios') }}" class="btn btn-outline-primary btn-lg btn-block" type="button">Nueva comparación</a>
        <a type="button" href="{{ route('compare.informes') }}"class="btn btn-outline-secondary btn-lg btn-block" type="button">Ver informes anteriores</a>
    </div>
</div>
@endsection
@section('footer_scripts')
@endsection
