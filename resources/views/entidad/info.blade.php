<div class="container">
    @if ($provincia)
    <li class="btn  btn-outline-primary" style="margin-bottom: 2px" >
        <a href="{{ url("/prov/{$provincia->id}") }}" ><h2> {{ $provincia->codigo }} -
    <b> {{ $provincia->nombre }} </b></h2></a>
    </li>
<p>
parte de  {{ count($provincia->operativos) }} operativos
            	@foreach($provincia->operativos as $operativo)
    		<li class="btn  btn-outline-secondary" style="margin-bottom: 1px" >
		     <a href="{{ url('/operativo/'.$operativo->id) }}">
           ({{ $operativo->nombre }}) {{ $operativo->observacion }} </a>
         </li>
		@endforeach
</p>
    @endif
    {{!! $svg !!}}
<div/>
