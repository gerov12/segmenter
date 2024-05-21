<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Provincia;
use Illuminate\Support\Facades\Log;
use Auth;
use Illuminate\Support\Facades\Http;
use SimpleXMLElement;

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
        $geom_feature = $feature['geometry'];
        if ($provinciaCoincidente->geometria !== null) {
            if($geom_feature !== null) {
                $estado_geom = "Calculo en desarrollo..."; //CALCULAR
            } else {
                $estado_geom = "No hay geometría cargada en el geoservicio";
            }
        } else {
            if($geom_feature !== null) {
                $estado_geom = "No hay geometría cargada en la BD";
            } else {
                $estado_geom = "No hay geometrías cargadas";
            }
        }
        return ['estado_geom' => $estado_geom, 'geom_feature' => json_encode($geom_feature)];
    }

    private function getFeatureTypeList()
    {
        $response = Http::get('https://geonode.indec.gob.ar/geoserver/ows', [
            'service' => 'wfs',
            'version' => '2.0.0',
            'request' => 'GetCapabilities',
            'section' => 'FeatureTypeList'
        ]);

        $xml = $response->body();

        // Parse XML
        $xmlObject = new SimpleXMLElement($xml);

        // Convert SimpleXMLElement object to array
        $array = json_decode(json_encode($xmlObject), true);

        // Get FeatureTypeList section
        $featureTypeList = $array['FeatureTypeList']['FeatureType'];

        return $featureTypeList;
    }

    public function listarCapas()
    {  
        $capas = self::getFeatureTypeList(); 
        return view('compare_geonode.layers')->with('capas', $capas);
    }

    public function listarAtributos(Request $request)
    {
        $capa = $request->input('capa');
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
            $comparacion = self::compararProvincias($codigo, $nombre, $capa); //
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
                return trim(strval($provincia->nombre)) == trim(strval($feature['properties'][$nombre]));
            });
            if (!$provinciasCoincidentes->isEmpty()) {
                $existe_nom = true;
                if ($existe_cod == false) {
                    $provinciaCoincidente = $provinciasCoincidentes->first();
                }
            }

            $estado_geom = "-";
            $geom_feature = null;
            if ($existe_cod) {
                if ($existe_nom) {
                    $estado = "OK";
                    $chequeo = self::chequearEstadoGeometrias($provinciaCoincidente, $feature);
                    $estado_geom = $chequeo['estado_geom'];
                    $geom_feature = $chequeo['geom_feature'];
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
                'geom_feature' => $geom_feature,
                'errores' => $errores
            ];
        }
        
        return ['resultados' => $resultados, 'elementos_erroneos' => $elementos_erroneos, 'total_errores' => $total_errores];
    }

    public function importarGeometria(Request $request) 
    {   
        $cod_provincia = $request->input('cod_provincia');
        $provincia = Provincia::where('codigo', $cod_provincia)->first();
        $geomFeature = $request->input('geom_feature');
        $id_new_geom = $provincia->setGeometriaAttribute($geomFeature);
        if ($id_new_geom !== null) {
            return response()->json(['statusCode'=> 200, 'id_geometria' => $id_new_geom]);
        } else {
            return response()->json(['statusCode'=> 500, 'id_geometria' => $id_new_geom]);
        }
        
    }
}
