@extends('layouts.app')

@section('content_main')
<div class="container justify-content-center" style="width: 40%">
    <div class="alert alert-warning alert-dismissible justify-content-center" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <b>ATENCIÓN:</b> Función en desarrollo. La comparación de la capa se realizará contra la tabla Provincia.
    </div>
    <div class="row justify-content-center">   
        <h3>Validación de Base de Datos con capas de Geonode</h3>
    </div>
    <br>
    <div class="row justify-content-center">
        <div class="card" style="width: 30rem;">
            <div class="card-header">
                Seleccionar capa para la comparación
            </div>
            <div class="card-body">
                <form action="{{ route('compare.atributos') }}" method="POST">
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
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
@section('footer_scripts')
@endsection
