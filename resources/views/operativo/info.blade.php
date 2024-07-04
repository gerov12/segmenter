<div class="container">
    @if ($operativo)
    <li class="btn  btn-outline-primary" style="margin-bottom: 2px" >
        <a href="{{ url("/operativo/{$operativo->id}") }}" ><h2> {{ $operativo->id }} -
    <b> {{ $operativo->nombre }} </b></h2></a>
    </li>
    @if ($operativo->observacion !== null)
        <p>Observación: {{$operativo->observacion}}</p>
    @endif
<p>
Realizado en  {{ count($operativo->provincias) }} provincias
            	@foreach($operativo->provincias as $provincia)
    		<li class="btn  btn-outline-secondary" style="margin-bottom: 1px" >
		     <a href="{{ url('/provincia/'.$provincia->id) }}">
           ({{ $provincia->codigo }}) {{ $provincia->nombre }} </a>
         </li>
		@endforeach
</p>
    @endif
</div>
