@extends('layouts.app')

@section('content_main')
<div class="container">
    <div class="row justify-content-center">
        <div class="card" style="width: 50rem;">
            <div class="card-header">
                Seleccionar campos para la capa <b>{{ $capa }}</b>
            </div>
            <div class="card-body">
                <form action="{{ route('compare.comparar', ['capa' => $capa]) }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-lg-6">
                            <label for="codigo">Código:</label>
                            <select name="codigo" id="codigo" class="form-control">
                                @foreach ($atributos as $atributo)
                                    <option value="{{ $atributo['name'] }}">{{ $atributo['name'] }} (tipo: {{explode(':', $atributo['type'])[1]}})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-6">
                            <label for="nombre">Nombre:</label>
                            <select name="nombre" id="nombre" class="form-control">
                                @foreach ($atributos as $atributo)
                                    <option value="{{ $atributo['name'] }}">{{ $atributo['name'] }} (tipo: {{explode(':', $atributo['type'])[1]}})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <br>
                    <button type="submit" class="btn btn-primary">Comparar</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
@section('footer_scripts')
@endsection
