<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Provincia;
use Illuminate\Support\Facades\Log;
use Auth;

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

    private function chequearEstadoGeometrias($provinciaCoincidente, $feature)
    {
        if ($provinciaCoincidente->geometria !== null) {
            if($feature['geometry'] !== null) {
                $estado_geom = "Calculo en desarrollo..."; //CALCULAR
            } else {
                $estado_geom = "No hay geometría cargada en el geoservicio";
            }
        } else {
            if($feature['geometry'] !== null) {
                $estado_geom = "No hay geometría cargada en la BD";
            } else {
                $estado_geom = "No hay geometrías cargadas";
            }
        }
        return $estado_geom;
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
            $comparacion = self::compararProvincias($codigo, $nombre, $capa);
            return view('compare_geonode.result')->with([
                'capa' => $capa, 
                'tabla' => "Provincia", //esto junto a la función que se llama para comparar dependerá de la capa
                'resultados' => $comparacion['resultados'], 
                'elementos_erroneos' => $comparacion['elementos_erroneos'], 
                'total_errores' => $comparacion['total_errores'], 
                'cod' => $codigo, 
                'nom' => $nombre,
                'operativo' => "-", //TO-DO
                'datetime' => date("d-m-Y (H:i:s)"),
                'usuario' => Auth::user()->name
            ]);
            
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
        $elementos_erroneos = 0;
        $total_errores = 0;

        foreach ($datos['features'] as $feature) {
            $provinciaCoincidente = null;
            $existe_cod = $existe_nom = false;
            $errores = 0;

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

            $estado_geom = "-";
            if ($existe_cod) {
                if ($existe_nom) {
                    $estado = "OK";
                    $estado_geom = self::chequearEstadoGeometrias($provinciaCoincidente, $feature);
                } else {
                    $estado = "Diferencia en el nombre";
                    $elementos_erroneos++;
                    $errores++;
                }
            } else {
                $elementos_erroneos++;
                $errores++;
                if ($existe_nom) {
                    $estado = "Diferencia en el código";
                } else {
                    $estado = "No hay coincidencias";
                    $errores++;
                }
            }

            $total_errores += $errores;

            $resultados[] = [
                'feature' => $feature,
                'provincia' => $provinciaCoincidente,
                'existe_cod' => $existe_cod,
                'existe_nom' => $existe_nom,
                'estado' => $estado,
                'estado_geom' => $estado_geom,
                'errores' => $errores
            ];
        }
        
        return ['resultados' => $resultados, 'elementos_erroneos' => $elementos_erroneos, 'total_errores' => $total_errores];
    }
}
