<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Geoservicio;

class GeoservicioController extends Controller
{   
    public function assembleURL($url){
        $parsedUrl = parse_url($url);
        $scheme = $parsedUrl['scheme'];
        $host = $parsedUrl['host'];
        $path = isset($parsedUrl['path']) ? $parsedUrl['path'] : '';
        $query = isset($parsedUrl['query']) ? $parsedUrl['query'] : '';

        $baseUrl = $scheme . '://' . $host . $path;

        // si el path no termina en /ows o /wfs, se agrega /ows al final
        if (!preg_match('/\/(ows|wfs)$/', $baseUrl)) {
            $baseUrl = rtrim($baseUrl, '/') . '/wfs'; //agrego wfs ya que ows no funciona para todos los geoservicios (ej: geoservicios.indec.gob.ar ó wms.ign.gob.ar)
        }

        // proceso los parámetros de consulta existentes
        parse_str($query, $queryParams);

        // filtrar para guardar solo los parámetros service y version
        $allowedParams = ['service', 'version'];
        $filteredQueryParams = array_filter(
            $queryParams,
            function($key) use ($allowedParams) {
                return in_array($key, $allowedParams);
            },
            ARRAY_FILTER_USE_KEY
        );

        // coloco los valores default para los parametros que no hayan sido especificados
        $filteredQueryParams['service'] = $filteredQueryParams['service'] ?? 'wfs';
        $filteredQueryParams['version'] = $filteredQueryParams['version'] ?? '2.0.0';

        // construyo la URL final con los parametros
        $finalUrl = $baseUrl . '?' . http_build_query($filteredQueryParams);
        return $finalUrl;
    }

    private function create(Request $request){
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:255',
            'url' => ['required', 'url']
        ]);

        $geoservicio = Geoservicio::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'url' => $this->assembleURL($request->url)
        ]);

        return $geoservicio;
    }

    public function store(Request $request)
    {
        $this::create($request);

        flash("Geoservicio guardado!")->success();
        return redirect()->route('compare.geoservicios');
    }

    public function storeAndConnect(Request $request)
    {
        $geoservicio = $this::create($request);

        $request = new Request();
        $request->merge(['geoservicio_id' => $geoservicio->id]);

        return app(CompareController::class)->inicializarGeoservicio($request);
    }
}
