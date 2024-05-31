<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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
        Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:255',
            'url' => ['required', 'url']
        ])->validateWithBag('new');

        $geoservicio = Geoservicio::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'url' => $this->assembleURL($request->url)
        ]);

        return $geoservicio;
    }

    private function update(Request $request){
        $geoservicio = Geoservicio::findOrFail($request->input('geoservicio_id'));
        session(['geoservicio' => $geoservicio]);
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:255',
            'url' => ['required', 'url']
        ])->validateWithBag('edit');

        $geoservicio->nombre = $request->nombre;
        $geoservicio->descripcion = $request->descripcion;
        $geoservicio->url = $this->assembleURL($request->url);
        $geoservicio->save();

        return $geoservicio;
    }

    public function store(Request $request)
    {
        $geoservicio_id = $request->input('geoservicio_id');
        if ($geoservicio_id === null){ //si estoy creando
            $this::create($request);
            $message = "Geoservicio guardado!";
        } else { //si estoy editando
            $geoservicio = $this::update($request);
            $message = "Geoservicio actualizado!";
        }
        
        flash($message)->success();
        return redirect()->route('compare.geoservicios');
    }

    public function storeAndConnect(Request $request)
    {
        $geoservicio_id = $request->input('geoservicio_id');
        if ($geoservicio_id === null){ //si estoy creando
            $geoservicio = $this::create($request);
        } else { //si estoy editando
            $geoservicio = $this::update($request);
        }

        $request = new Request();
        $request->merge(['geoservicio_id' => $geoservicio->id]);

        return app(CompareController::class)->inicializarGeoservicio($request);
    }

    public function delete(Request $request)
    {
        $geoservicio = Geoservicio::findOrFail($request->input('geoservicio_id'));
        //si el geoservicio es utilizado en algun informe guardar su nombre y url
        $geoservicio->delete();

        flash("Geoservicio eliminado correctamente.")->success();
        return redirect()->route('compare.geoservicios');
    }
}
