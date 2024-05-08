<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Provincia;
use Illuminate\Support\Facades\Log;

class CompareController extends Controller
{
    private function getCapa($capa)
    {
        $url= 'https://geonode.indec.gob.ar/geoserver/ows?service=wfs&version=2.0.0&request=GetFeature&resultType=results&outputFormat=application/json&typeName='.$capa;
        $result = file_get_contents($url);
        $datos = json_decode($result, true);
        return $datos;
    }

    private function getAtributos($capa)
    {
        $url= 'https://geonode.indec.gob.ar/geoserver/wfs?request=DescribeFeatureType&outputFormat=application/json&typeNames='.$capa;
        $result = file_get_contents($url);
        $datos = json_decode($result, true);
        return $datos;
    }

    public function listarAtributos($capa)
    {
        $datos = self::getAtributos($capa);
        $atributos = [];
        if(isset($datos['featureTypes'][0]['properties'])) {
            $atributos = $datos['featureTypes'][0]['properties'];
        }
        return view('compare_geonode.properties')->with(['capa' => $capa, 'atributos' => $atributos]);
    }

    public function comparar(Request $request, $capa)
    {
        $codigo = $request->input('codigo');
        $nombre = $request->input('nombre');

        if ($codigo != $nombre) {
            $resultados = self::compararProvincias($codigo, $nombre, $capa);
            return view('compare_geonode.result')->with(['capa' => $capa, 'tabla' => "Provincia", 'resultados' => $resultados, 'cod' => $codigo, 'nom' => $nombre]);
            
        } else {
            flash("Los campos seleccionados deben ser diferentes")->error();
            return back();
        }
    }

    private function compararProvincias($codigo, $nombre, $capa)
    {
        $datos = self::getCapa($capa);
        $provincias = Provincia::all();

        $resultados = [];
        $provincias_erroneas = 0;
        $total_errores = 0;

        foreach ($datos['features'] as $feature) {
            $provinciaCoincidente = null;
            $existe_cod = $existe_nom = false;

            $provinciasCoincidentes = $provincias->filter(function ($provincia) use ($feature, $codigo) {
                return trim(strval($provincia->codigo)) == trim(strval($feature['properties'][$codigo]));
            });
    
            if (!$provinciasCoincidentes->isEmpty()) {
                $existe_cod = true;
                $provinciaCoincidente = $provinciasCoincidentes->first();
            }

            $provinciasCoincidentes = $provincias->filter(function ($provincia) use ($feature, $nombre) {
                Log::debug(strval($provincia->nombre)." / ".strval($feature['properties'][$nombre]));
                return trim(strval($provincia->nombre)) == trim(strval($feature['properties'][$nombre]));
            });
            if (!$provinciasCoincidentes->isEmpty()) {
                $existe_nom = true;
                if ($existe_cod == false) {
                    $provinciaCoincidente = $provinciasCoincidentes->first();
                }
            }

            if ($existe_cod) {
                if ($existe_nom) {
                    $estado = "OK";
                    //dd($provinciaCoincidente->geometria,$feature['geometry']);
                    // MOSTRAR DIFERENCIA EN GEOMETRIAS
                } else {
                    $estado = "Diferencia en el nombre";
                    $provincias_erroneas++;
                    $total_errores++;
                }
            } else {
                $provincias_erroneas++;
                $total_errores++;
                if ($existe_nom) {
                    $estado = "Diferencia en el código";
                } else {
                    $estado = "No hay coincidencias";
                    $total_errores++;
                }
            }

            $resultados[] = [
                'feature' => $feature,
                'provincia' => $provinciaCoincidente,
                'existe_cod' => $existe_cod,
                'existe_nom' => $existe_nom,
                'estado' => $estado,
                'provincias_erroneas' => $provincias_erroneas,
                'total_errores' => $total_errores
            ];
        }
        
        return $resultados;
    }
}
