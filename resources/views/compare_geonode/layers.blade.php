@extends('layouts.app')

@section('content_main')
<div class="container">
    <div class="row justify-content-center">
        <div class="card" style="width: 30rem;">
            <div class="card-header">
                Seleccionar capa para la comparación
            </div>
            <div class="card-body">
                <form action="{{ route('compare.atributos') }}" method="POST">
                    @csrf
                    <div class="row justify-content-center">
                        <div class="col-lg-10">  
                            <label for="capa">Capa:</label>
                            <select name="capa" id="capa" class="form-control">
                                @foreach ($capas as $capa)
                                    <option value="{{ $capa['name'] }}">{{ $capa['name'] }}</option>
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
