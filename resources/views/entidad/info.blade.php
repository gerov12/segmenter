<div class="container">
   <ol>
    @if ($provincia)
    Provincia:
    <a href="{{ url("/prov/{$provincia->id}") }}" >
      <li class="btn  btn-outline-primary" style="margin-bottom: 2px" >
      <h2> ({{ $provincia->codigo }}) -
      <b> {{ $provincia->nombre }} </b></h2>
    </li>
   </a>
    @endif
    @if ($entidad)
    <br /> Departamento:
    <a href="{{ url("/depto/{$entidad->localidad->departamentos->first()->id}") }}" >
      <li class="btn  btn-outline-secondary" style="margin-bottom: 1px" >
          <h5>
            ({{ $entidad->localidad->departamentos->first()->codigo }})
            {{ $entidad->localidad->departamentos->first()->nombre }}
          </h5>
        </li>
      </a>
      <br /> Localidad:
      <a href="{{ url("/localidad/{$entidad->localidad->id}") }}" >
      <li class="btn  btn-outline-secondary" style="margin-bottom: 1px" >
        <h4>
          ({{ $entidad->localidad->codigo }})
          {{ $entidad->localidad->nombre }}
        </h4>
      </li>
    </a>
      <br />Entidad:<a href="{{ url("/entidad/{$entidad->id}") }}" >
      <li class="btn  btn-outline-info" style="margin-bottom: 1px" >
        <h3>
          ({{ $entidad->codigo }})
          {{ $entidad->nombre }}
        </h3>
      </li>
    </a>
    @endif
    </ol>
    {!! $svg !!}
  </div>
