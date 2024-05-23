<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Geoservicio;

class GeoservicioController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:255',
            'url' => 'required|url',
            'tipo' => 'required|string',
        ]);

        Geoservicio::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'url' => $request->url,
            'tipo' => $request->tipo,
        ]);

        flash("Geoservicio guardado!")->success();
        return redirect()->route('compare.geoservicios');
    }

    public function storeAndConnect(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:255',
            'url' => 'required|url',
            'tipo' => 'required|string',
        ]);

        $geoservicio = Geoservicio::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'url' => $request->url,
            'tipo' => $request->tipo,
        ]);

        $request = new Request();
        $request->merge(['geoservicio_id' => $geoservicio->id]);

        return app(CompareController::class)->inicializarGeoservicio($request);
    }
}
