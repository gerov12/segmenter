@extends('layouts.app')
@section('content')
<div class="text-center">
<h2 class="text-center">Histograma</h2>
@if (isset($titulo))
<h3 class="text-center">({{ $titulo }})</h3>
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
<div id ="resumen"></div>
<canvas id="canvas" style="padding: 20px 50px 20px 50px; max-height: 500px; " height="280" width="600"></canvas>
</div>
@endsection
@section('footer_scripts')
        <script>
        var myChart = '';
        var Cantidad = new Array();
      @if (isset($url_data))
        var url = "{{$url_data}}";
      @endif
      @if (isset($aglomerado))
        var url = "{{url('ver-segmentacion-grafico-resumen')}}/{{$aglomerado->id}}";
      @endif
      @if (isset($localidad))
        var url = "{{url('localidad')}}/{{$localidad->id}}/grafico";
      @endif
        var Hechos = new Array();
        var Provs = new Array();
        var Labels = new Array();
        var Viviendas = new Array();
        var Detalle = new Array();
        var radiosDataSet = new Array();
        var newDataset = new Array();

        const COLORS = [
          '#4dc9f6',
          '#f67019',
          '#f53794',
          '#537bc4',
          '#acc236',
          '#166a8f',
          '#00a950',
          '#58595b',
          '#8549ba',
          '#4dc911',
          '#f67011',
          '#f53711',
          '#537b11',
          '#acc211',
          '#166a11',
          '#00a911',
          '#585911',
          '#854911',
          '#4d22f6',
          '#f62219',
          '#f52294',
          '#5322c4',
          '#ac2236',
          '#16228f'
        ];

        function fcolor(index) {
          index=(parseInt(index)+2)/4;
          return COLORS[index % COLORS.length];
        };
        $(document).ready(function(){
          $.post(url, {"_token": "{{ csrf_token() }}"},function(response){
            var sum = 0;
            var n_cants= 0;
            response.forEach(function(data){

              const resultado = newDataset.find( prov => prov.label === data.prov );
              if (resultado){
                resultado.data.push( 
                    {x:data.hecho,y:data.cant}
                );
                newDataset.push(resultado);
              
              }else{
                newDataset.push( {
                    label: data.prov,
                    borderColor: fcolor(data.prov),
                    data: [{x:data.hecho,y:data.cant}]
                });
              }
                Cantidad.push(data.cant);
                Hechos.push(data.hecho); 
                Provs.push(data.prov);
                radiosDataSet.push({label:data.prov,data:{x:data.hecho,y:data.cant}});
                sum += Number(data.cant);
                n_cants++;
                Detalle.push(data.detalle);
            });
            var mensaje = sum+' en '+n_cants+' días, con un promedio de '+Math.round (sum/n_cants)+' x día';
            document.getElementById("resumen").innerHTML=mensaje;
            var ctx = document.getElementById("canvas").getContext('2d');
                var myChart = new Chart(ctx, {
                  type: 'line',
                  data: {
                      labels: Hechos,
                      datasets: 
//[
//                      {
//                          label: Provs, //'Radios',
//                          data: Cantidad,
//                      }
newDataset
//]
                  },
                  options: {
                      responsive: true,
                      scales: {
                          x: {
                               type: 'time',
                               time: {
                                    // Luxon format string
                                    tooltipFormat: 'dd-MM '
//                                    unit: 'day'
                               },
                               display: true,
                               offset: true,
                               text: 'Fecha'
                          },
                          y: {
                              title: 'Radios ',
                              grid: {
                                  drawBorder: true,
                                  color:
                                  function (context) {
                                   const colores = [
                                      'rgb(255, 99, 132)',
                                      'rgb(255, 159, 64)',
                                      'rgb(255, 205, 86)',
                                      'rgb(75, 192, 192)',
                                      'rgb(54, 162, 235)',
                                      'rgb(153, 102, 255)',
                                      'rgb(231,233,237)'
                                    ];
                                   return colores[context.tick.value % 7];
                                  }
                              },
                              ticks: {
                                  // valores enteros
                                  precision: 0, 
                                  beginAtZero:true
                              },
                              title: {
                                display: true,
                                text: 'Cantidad'
                              }
                          }
                      }
                  }
              });
          });
        });
        </script>
@endsection
