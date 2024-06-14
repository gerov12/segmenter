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
    @if ($segmento)
    @if ($segmento->localidad)
    <br /> Departamento:
    <a href="{{ url("/depto/{$segmento->localidad->departamentos->first()->id}") }}" >
      <li class="btn  btn-outline-secondary" style="margin-bottom: 1px" >
          <h5>
            ({{ $segmento->localidad->departamentos->first()->codigo }})
            {{ $segmento->localidad->departamentos->first()->nombre }}
          </h5>
        </li>
      </a>

      <br /> Localidad:
      <a href="{{ url("/localidad/{$segmento->localidad->id}") }}" >
      <li class="btn  btn-outline-secondary" style="margin-bottom: 1px" >
        <h4>
          ({{ $segmento->localidad->codigo }})
          {{ $segmento->localidad->nombre }}
        </h4>
      </li>
    </a>
    @endif
      <br />Segmento:<a href="{{ url("/segmento/{$segmento->id}") }}" >
      <li class="btn  btn-outline-info" style="margin-bottom: 1px" >
        <h3>
          ({{ $segmento->codigo }})
          {{ $segmento->nombre }}
        </h3>
      </li>
    </a>
    <br />
    {{ $segmento->toJson(JSON_PRETTY_PRINT) }}
    @endif
    </ol>
    @if (isset($svg))
    {!! $svg !!}
    @endif
  </div>
