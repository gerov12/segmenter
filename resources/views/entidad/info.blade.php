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
    @if ($entidad)
      <ul>
        <li class="btn  btn-outline-secondary" style="margin-bottom: 1px" >
          <h5> Departamento:
            <a href="{{ url("/depto/{$entidad->localidad->departamentos->first()->id}") }}" >
            ({{ $entidad->localidad->departamentos->first()->codigo }})
            {{ $entidad->localidad->departamentos->first()->nombre }}
            </a>
          </h5>
        </li>
        <br />
      <li class="btn  btn-outline-secondary" style="margin-bottom: 1px" >
        <h4> Localidad:
          <a href="{{ url("/localidad/{$entidad->localidad->id}") }}" >
          ({{ $entidad->localidad->codigo }})
          {{ $entidad->localidad->nombre }}
          </a>
        </h4>
      </li>
      <br />
      <li class="btn  btn-outline-info" style="margin-bottom: 1px" >
        <h3> Entidad:
          <a href="{{ url("/entidad/{$entidad->id}") }}" >
          ({{ $entidad->codigo }})
          {{ $entidad->nombre }}
          </a>
        </h3>
      </li>
    </ul>
    @endif

    {!! $svg !!}
<div/>
