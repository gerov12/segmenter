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

@isset($datos)
<table class="table">
    <thead>
        <tr>
            <th>Prov</th>
            <th>Nom Prov</th>
            <th>Dpto</th>
            <th>Nom Depto</th>
            <th>codloc</th>
            <th>Nom Loc</th>
            <th>Fraccion</th>
            <th>Radio</th>
            <th>Segmento</th>
            <th>viviendas</th>
            <th>¿Existe?</th>
    
            
          
        </tr>
    </thead>

    <tbody>
        @foreach ($datos as $fila)
            <tr>
                <td>{{ $fila['prov'] }}</td>
                <td>{{ $fila['nom_prov']  ?? $fila['nomprov']}}</td>
                <td>{{ $fila['dpto'] ?? $fila['depto'] }}</td>
                <td>{{$fila['nom_dpto'] ?? $fila['nomdepto']}}</td>
                <td>{{$fila['codloc']}}</td>
                <td>{{$fila['nom_loc'] ?? $fila['nomloc'] }}</td>
                <td>{{$fila['frac']}}</td>
                <td>{{$fila['radio']}}</td>
                <td>{{$fila['seg']}}</td>
                <td>{{$fila['viviendas']}}</td>
                <td>{{ isset($fila['existe']) ? ($fila['existe'] ? 'Sí' : 'No') : 'No' }}</td>
            </tr>
        @endforeach

    </tbody>
</table>
<form action="/cargarsegmentos" method="post" enctype="multipart/form-data">
    @csrf
    <input type="file" name="tabla_segmentos" hidden>
    <button type="submit">Confirmar importación</button>
</form>
@endisset
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
