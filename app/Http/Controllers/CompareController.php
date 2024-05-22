<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Provincia;
use App\Model\Geoservicio;
use App\Model\Informe;
use App\Model\InformeProvincia;
use Illuminate\Support\Facades\Log;
use Auth;
use Carbon\Carbon;
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

    private function getCapas()
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

    public function verMenu()
    {
        return view('compare_bd.menu');
    }

    public function listarInformes()
    {  
        $informes = Informe::all(); 
        foreach ($informes as $informe) {
            $informe->datetime = Carbon::parse($informe->datetime);
        }
        return view('compare_bd.informes')->with('informes', $informes);
    }

    public function verInforme($informe)
    {
        $informe = Informe::findOrFail($informe);
        $resultados = InformeProvincia::where('informe_id', $informe->id)->get();
        return view('compare_bd.informe')->with([
            'capa' => $informe->capa, 
            'tabla' => $informe->tabla,
            'resultados' => $resultados, 
            'geometrias' => null,
            'elementos_erroneos' => $informe->elementos_erroneos, 
            'total_errores' => $informe->total_errores, 
            'cod' => $informe->cod, 
            'nom' => $informe->nom,
            'operativo' => $informe->operativo,
            'datetime' => Carbon::parse($informe->datetime),
            'usuario' => $informe->user,
            'tipo_informe' => "informe"
        ]); 
    }

    public function listarGeoservicios()
    {  
        $geoservicios = Geoservicio::all(); 
        return view('compare_bd.geoservicios')->with('geoservicios', $geoservicios);
    }

    public function inicializarGeoservicio(Request $request)
    {
        $geoservicio = $request->input('geoservicio');
        //if testear conexión: inicializo, else: flash informando y vuelvo atrás
    }

    public function listarCapas()
    {  
        $capas = self::getCapas(); 
        return view('compare_bd.layers')->with('capas', $capas);
    }

    public function listarAtributos(Request $request)
    {
        $capa = $request->input('capa');
        $datos = self::getAtributos($capa);
        $atributos = [];
        if(isset($datos['featureTypes'][0]['properties'])) {
            $atributos = $datos['featureTypes'][0]['properties'];
        }
        return view('compare_bd.properties')->with(['capa' => $capa, 'atributos' => $atributos]);        
    }

    public function comparar(Request $request, $capa)
    {
        $codigo = $request->input('codigo');
        $nombre = $request->input('nombre');

        if ($codigo != $nombre) {
            $comparacion = self::compararProvincias($codigo, $nombre, $capa); //
            $resultados_sin_geom = [];
            $geometrias = [];

            foreach ($comparacion['resultados'] as $resultado) {
                $feature = $resultado['feature'];
                $id = $feature['id'];
                
                // modifico el campo feature del resultado para que solo tenga id y properties
                $resultado['feature'] = [
                    'id' => $id,
                    'properties' => $feature['properties']
                ];
                $resultados_sin_geom[] = $resultado;
                
                // guardo la geometría del feature del resultado por separado
                $geometrias[$id] = $feature['geometry'];
            }
            return view('compare_bd.informe')->with([
                'capa' => $capa, 
                'tabla' => "Provincia", //esto junto a la función que se llama para comparar dependerá de la capa
                'resultados' => $resultados_sin_geom, 
                'geometrias' => $geometrias,
                'elementos_erroneos' => $comparacion['elementos_erroneos'], 
                'total_errores' => $comparacion['total_errores'], 
                'cod' => $codigo, 
                'nom' => $nombre,
                'operativo' => "-", //TO-DO
                'datetime' => Carbon::now(),
                'usuario' => Auth::user(),
                'tipo_informe' => "resultado"
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

    public function storeInforme(Request $request)
    {
        $informe = Informe::create([
            'capa' => $request->input('capa'),
            'tabla' => $request->input('tabla'),
            'elementos_erroneos' => $request->input('elementos_erroneos'),
            'total_errores' => $request->input('total_errores'),
            'cod' => $request->input('cod'),
            'nom' => $request->input('nom'),
            'operativo_id' => null,
            'datetime' => $request->input('datetime'),
            'user_id' => $request->input('user_id'),
        ]);
        
        //guardo los resultados del informe (por el momento solo para provincias)
        foreach ($request->input('resultados') as $resultado) {
            $provincia_id = isset($resultado['provincia']) ? intval($resultado['provincia']['id']) : null; //null si no existe en la bd
            $informe_provincia = new InformeProvincia([
                'informe_id' => $informe->id,
                'provincia_id' => $provincia_id,
                'existe_cod' => $resultado['existe_cod'],
                'existe_nom' => $resultado['existe_nom'],
                'estado' => $resultado['estado'],
                'estado_geom' => $resultado['estado_geom'],
                'errores' => $resultado['errores'],
                'cod' => $resultado['feature']['properties'][$request->input('cod')],
                'nom' => $resultado['feature']['properties'][$request->input('nom')]
            ]);
            $informe_provincia->save();
        }
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
