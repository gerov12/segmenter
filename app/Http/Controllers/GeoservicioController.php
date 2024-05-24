<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Geoservicio;

class GeoservicioController extends Controller
{   
    private function create(Request $request){
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:255',
            'url' => ['required', 'url', 'regex:/\/$/'],
            'tipo' => 'required|string',
        ]);

        $geoservicio = Geoservicio::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'url' => $request->url,
            'tipo' => $request->tipo,
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
