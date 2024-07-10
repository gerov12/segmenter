@extends('layouts.app')
@section('content')
<div class="text-center">
@if (isset($titulo))
<h3 class="text-center">{{ $titulo }}</h3>
@else
<h2 class="text-center">Histograma</h2>
@endif
@if (isset($subtitulo))
<h5 class="text-center">({{ $subtitulo }})</h5>
@endif
@if (isset($provincia))
<h3 class="text-center">({{ $provincia->codigo }}) {{ $provincia->nombre }}</h3>
@endif
@if (isset($aglomerado))
<h3 class="text-center">Aglomerado ({{ $aglomerado->codigo }}) {{ $aglomerado->nombre }}</h3>
@endif
@if (isset($localidad))
<h4 class="text-center">Localidad ({{ $localidad->codigo }}) {{ $localidad->nombre }}</h4>
@endif
@if (isset($departamento))
<h5 class="text-center">{{ $departamento->nombre }}</h5>
@endif
<div id ="resumen"></div>
<div class="container">
  @include('flash::message')
</div>
@endsection
