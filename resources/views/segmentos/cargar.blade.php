@extends('layouts.app')

@section('content')
<div class="container">
   <h4 class="center"> Carga listado de segmentos con viviendas.</h4>
    <div class="row justify-content-center">
        <div class="center-block">
            @isset($data)
                <div class="alert alert-primary" role="alert">
                    <ul>
      @foreach ($data as $index => $value)
				@if (isset($data['file']))
                   	<p>El usuario {{Auth::user()->name}} subió los siguientes archivos:</p>
                       	@foreach ($value as $index_file => $value_file)
  	                    		<li>{{$index_file}} -> {{$value_file}}</li>
           				@endforeach
				@else 
	                    <p>Y estas otras cosas... :</p>
               	        <li>{{$index}} -> {{$value}}</li>
				@endif
        @if ($loop->last)
        	The current UNIX timestamp is {{ time() }}. {{ date('Y-m-d H:m:s') }} UTC.
        @endif
     @endforeach
                    </ul>
                </div>
            @endisset
            <form action="/cargarSegmentos" method="POST" enctype="multipart/form-data" class="form-horizontal ">
                @csrf
                <div class="form-group row bg-success">
                    <label for="tabla_segmentos" class="col-sm-4 col-form-label ">Archivo de segmentos a cargar </label>
                    <div class="col-sm-8">
                        <input type="file" class="form-control-file" id="tabla_segmentos" name="tabla_segmentos">
                    </div>
            		</div>
		<div class="form-group">
		  <div class="text-center">
                     <button type="submit" class="btn btn-primary">Enviar</button>
                    </div>
		</div>
            </form>
        </div>
    </div>
</div>

<!--
<h1>Cargar segmentos</h1>

<form action="/cargarSegmentos" method="post" enctype="multipart/form-data">
    @csrf

    <input type="file" name="tabla_segmentos" accept=".xlsx, .xls, .csv">

    <button type="submit">Importar</button>
</form>
-->


@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif




@endsection
