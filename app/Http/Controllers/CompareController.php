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
    protected $geoservicio;
    private $tempInforme;
    private $tempResultados = [];
    private $tempGeometrias = [];

    private function chequearEstadoGeometrias($provinciaCoincidente, $feature=null, $feature_geometry=null)
    {   
        if ($provinciaCoincidente->geometria !== null) {
            if ($feature !== null) {
                if ($feature['geometry'] !== null) {
                    $estado_geom = "Calculo en desarrollo..."; //CALCULAR
                } else {
                    $estado_geom = "No hay geometría cargada en el geoservicio";
                }
            } else if ($feature_geometry !== null){ //si entré por importarGeometria (el if debería dar siempre true)
                $estado_geom = "Calculo en desarrollo..."; //CALCULAR
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

    public function verMenu()
    {
        try {
            $user = Auth::user();
            if ($user->can('Generar Informes') || $user->can('Ver Informes')) {
                return view('compare_bd.menu');
            } else {
                flash("No tienes permiso para hacer eso.")->error();
                return back();
            }
        } catch (PermissionDoesNotExist $e) {
            flash('message', 'No existe el permiso "Generar Informes" o "Ver Informes"')->error();
        }
    }

    public function listarInformes()
    {  
        try {
            if (Auth::user()->can('Ver Informes')) {
                $informes = Informe::with('user')->get(); 
                foreach ($informes as $informe) {
                    $informe->datetime = Carbon::parse($informe->datetime);
                }
                return view('compare_bd.informes')->with('informes', $informes);
            } else {
                flash("No tienes permiso para hacer eso.")->error();
                return back();
            }
        } catch (PermissionDoesNotExist $e) {
            flash('message', 'No existe el permiso "Ver Informes"')->error();
        }
    }

    public function verInforme($informe)
    {
        try {
            if (Auth::user()->can('Ver Informes')) {
                $informe = Informe::findOrFail($informe);
                $resultados = InformeProvincia::where('informe_id', $informe->id)->get();
                return view('compare_bd.informe')->with([
                    'capa' => $informe->capa, 
                    'tabla' => $informe->tabla,
                    'resultados' => $resultados, 
                    'elementos_erroneos' => $informe->elementos_erroneos, 
                    'total_errores' => $informe->total_errores, 
                    'cod' => $informe->cod, 
                    'nom' => $informe->nom,
                    'operativo' => $informe->operativo,
                    'datetime' => Carbon::parse($informe->datetime),
                    'usuario' => $informe->user,
                    'geoservicio' => $informe->geoservicio,
                    'tipo_informe' => "informe",
                    'informe_id' => $informe->id
                ]); 
            } else {
                flash("No tienes permiso para hacer eso.")->error();
                return back();
            }
        } catch (PermissionDoesNotExist $e) {
            flash('message', 'No existe el permiso "Ver Informes"')->error();
        }
    }

    public function listarGeoservicios()
    {  
        try {
            if (Auth::user()->can('Generar Informes')) {
                $geoservicios = Geoservicio::all(); 
                return view('compare_bd.geoservicios')->with('geoservicios', $geoservicios);
            } else {
                flash("No tienes permiso para hacer eso.")->error();
                return back();
            }
        } catch (PermissionDoesNotExist $e) {
            flash('message', 'No existe el permiso "Generar Informes"')->error();
        }
    }

    public function inicializarGeoservicio(Request $request)
    {   
        try {
            if (Auth::user()->can('Generar Informes')) {
                $geoservicio_id = $request->input('geoservicio_id');
                if ($geoservicio_id === null){ //si es una conexión rapida y por ende no envío el id de un geoservicio existente
                    $request->validate([
                        'nombre' => 'required|string|max:255',
                        'descripcion' => 'nullable|string|max:255',
                        'url' => ['required', 'url']
                    ]);
            
                    $geoservicio = new Geoservicio([ //distinto de Geoservicio::create, en este caso NO se almacena en la BD, es una conexión rápida
                        'nombre' => $request->nombre,
                        'descripcion' => $request->descripcion,
                        'url' => app(GeoservicioController::class)->assembleURL($request->url)
                    ]);
                    
                } else {
                    $geoservicio = Geoservicio::find($request->input('geoservicio_id'));
                }

                $connection_status = $geoservicio->testConnection();
                if ($connection_status['status']) {
                    return redirect()->route('compare.capas', ['geoservicio' => json_encode($geoservicio)])->with('message', 'Conexión existosa con el Geoservicio!');
                } else {
                    flash($connection_status['message'])->error();
                    $geoservicios = Geoservicio::all(); 
                    return view('compare_bd.geoservicios')->with('geoservicios', $geoservicios);
                }
            } else {
                flash("No tienes permiso para hacer eso.")->error();
                return back();
            }
        } catch (PermissionDoesNotExist $e) {
            flash('message', 'No existe el permiso "Generar Informes"')->error();
        }
    }

    public function listarCapas(Request $request)
    {  
        try {
            if (Auth::user()->can('Generar Informes')) {
                $geoservicioJson = $request->query('geoservicio');
                $geoservicioData = json_decode($geoservicioJson, true);
                if ($geoservicioData) {
                    $geoservicio = new Geoservicio($geoservicioData);
                    $result = $geoservicio->getCapas(); 
                    if ($result['status']) {
                        return view('compare_bd.layers', ['geoservicio' => $geoservicio])->with('capas', $result['capas']);
                    } else {
                        return redirect()->route('compare.geoservicios')->with('error', $result['message']);
                    }
                } else {
                    return redirect()->route('compare.geoservicios')->with('error', 'No hay Geoservicio seleccionado');
                }
            } else {
                flash("No tienes permiso para hacer eso.")->error();
                return back();
            }
        } catch (PermissionDoesNotExist $e) {
            flash('message', 'No existe el permiso "Generar Informes"')->error();
        }
    }

    public function listarAtributos(Request $request)
    {   
        try {
            if (Auth::user()->can('Generar Informes')) {
                $geoservicioJson = $request->query('geoservicio');
                $geoservicioData = json_decode($geoservicioJson, true);
                if ($geoservicioData) {
                    $geoservicio = new Geoservicio($geoservicioData);
                    $capa = $request->input('capa');
                    $result = $geoservicio->getAtributos($capa); 
                    if ($result['status']) {
                        $datos = $result['atributos'];
                        $atributos = [];
                        if(isset($datos['featureTypes'][0]['properties'])) {
                            $atributos = $datos['featureTypes'][0]['properties'];
                        }
                        return view('compare_bd.properties', ['geoservicio' => $geoservicio])->with(['capa' => $capa, 'atributos' => $atributos]);
                    } else {
                        return redirect()->route('compare.geoservicios')->with('error', $result['message']);
                    } 
                } else {
                    return redirect()->route('compare.geoservicios')->with('error', 'No hay Geoservicio seleccionado');
                } 
            } else {
                flash("No tienes permiso para hacer eso.")->error();
                return back();
            }
        } catch (PermissionDoesNotExist $e) {
            flash('message', 'No existe el permiso "Generar Informes"')->error();
        } 
    }

    public function validar(Request $request, $capa)
    {
        try {
            $user = Auth::user();
            if ($user->can('Generar Informes')) {
                $codigo = $request->input('codigo');
                $nombre = $request->input('nombre');

                if ($codigo != $nombre) {
                    $geoservicioJson = $request->query('geoservicio');
                    $geoservicioData = json_decode($geoservicioJson, true);
                    if ($geoservicioData) {
                        $geoservicio = new Geoservicio($geoservicioData);
                        return $this->obtenerResultados($geoservicio, $codigo, $nombre, $capa);
                    } else {
                        return redirect()->route('compare.geoservicios')->with('error', 'No hay Geoservicio seleccionado');
                    }
                } else {
                    flash("Los campos seleccionados deben ser diferentes")->error();
                    return back();
                }
            } else {
                flash("No tienes permiso para hacer eso.")->error();
                return back();
            }
        } catch (PermissionDoesNotExist $e) {
            flash('message', 'No existe el permiso "Generar Informes"')->error();
        }
    }

    private function obtenerResultados($geoservicio, $codigo, $nombre, $capa)
    {
        $comparacion = $this::compararProvincias($geoservicio, $codigo, $nombre, $capa); //

        foreach ($comparacion['resultados'] as $resultado) {
            $feature = $resultado['feature'];
            $provincia = $resultado['provincia'];
            
            // modifico el campo feature del resultado para que solo tenga id y properties
            $resultado['feature'] = [
                'id' => $feature['id'],
                'properties' => $feature['properties']
            ];
            $this->tempResultados[] = $resultado;
            
            // si la provincia existe en la BD guardo la geometría del feature correspondiente del resultado por separado
            if ($provincia) {
                $this->tempGeometrias[$provincia['codigo']] = $feature['geometry'];
            }
        }

        // creo el objeto temporal Informe en caso de que se quiera guardar
        $geoservicio_id = $geoservicio['id'] ?? null;
        $geoservicio_url = $geoservicio_id ? null : $geoservicio['url']; //solo para las conexiones rapidas guardo la url ya que no hay id
        $geoservicio_nombre = $geoservicio_id ? null : $geoservicio['nombre']; //solo para las conexiones rapidas guardo el nombre ya que no hay id
        $geoservicio_descripcion = $geoservicio_id ? null : $geoservicio['descripcion']; //solo para las conexiones rapidas guardo la url ya que no hay id
        $this->tempInforme = new Informe([
            'capa' => $capa,
            'tabla' => "Provincia", //esto junto a la función que se llama para comparar dependerá de la capa
            'elementos_erroneos' => $comparacion['elementos_erroneos'], 
            'total_errores' => $comparacion['total_errores'], 
            'cod' => $codigo, 
            'nom' => $nombre,
            'operativo_id' => null,
            'datetime' => Carbon::now(),
            'user_id' => Auth::user()->id,
            'geoservicio_id' => $geoservicio_id,
            'geoservicio_url' => $geoservicio_url,
            'geoservicio_nombre' => $geoservicio_nombre,
            'geoservicio_descripcion' => $geoservicio_descripcion
        ]);

        // Guardo los objetos en la sesión ya que no persisten (for each request, a new instance of your controller is created)
        session(['tempInforme' => $this->tempInforme, 'tempResultados' => $this->tempResultados, 'tempGeometrias' => $this->tempGeometrias]);
        return view('compare_bd.informe')->with([
            'capa' => $capa, 
            'tabla' => $this->tempInforme->tabla,
            'resultados' => $this->tempResultados, 
            'elementos_erroneos' => $this->tempInforme->elementos_erroneos, 
            'total_errores' => $this->tempInforme->total_errores, 
            'cod' => $this->tempInforme->cod, 
            'nom' => $this->tempInforme->nom,
            'operativo' => "-", //TO-DO
            'datetime' => $this->tempInforme->datetime,
            'usuario' => Auth::user(),
            'geoservicio' => $geoservicio,
            'tipo_informe' => "resultado"
        ]);  
    }

    private function compararProvincias(Geoservicio $geoservicio, $codigo, $nombre, $capa)
    {   
        try {
            if (Auth::user()->can('Generar Informes')) {
                if ($geoservicio) {
                    $result = $geoservicio->getCapa($capa); 
                    if ($result['status']) {
                        $datos = $result['capa'];
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
                                    $estado_geom = $this::chequearEstadoGeometrias($provinciaCoincidente, $feature);
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
                    } else {
                        return redirect()->route('compare.geoservicios')->with('error', $result['message']);
                    }
                } else {
                    return redirect()->route('compare.geoservicios')->with('error', 'No hay Geoservicio seleccionado');
                }
            } else {
                flash("No tienes permiso para hacer eso.")->error();
                return back();
            }
        } catch (PermissionDoesNotExist $e) {
            flash('message', 'No existe el permiso "Generar Informes"')->error();
        }
    }

    public function storeInforme()
    {   
        try {
            if (Auth::user()->can('Generar Informes')) {
                $this->tempInforme = session('tempInforme');
                $this->tempResultados = session('tempResultados');

                //guardo el informe
                $this->tempInforme->save();

                //guardo los resultados del informe (por el momento solo para provincias)
                foreach ($this->tempResultados as $resultado) {
                    $provincia_id = isset($resultado['provincia']) ? intval($resultado['provincia']['id']) : null; //null si no existe en la bd
                    $informe_provincia = new InformeProvincia([
                        'informe_id' => $this->tempInforme->id,
                        'provincia_id' => $provincia_id,
                        'existe_cod' => $resultado['existe_cod'],
                        'existe_nom' => $resultado['existe_nom'],
                        'estado' => $resultado['estado'],
                        'estado_geom' => $resultado['estado_geom'],
                        'errores' => $resultado['errores'],
                        'cod' => $resultado['feature']['properties'][$this->tempInforme->cod],
                        'nom' => $resultado['feature']['properties'][$this->tempInforme->nom]
                    ]);
                    $informe_provincia->save();
                }
                return response()->json(['success' => true]);
            } else {
                return response()->json(['error' => 'No tienes permiso para hacer eso.'], 403);
            }
        } catch (PermissionDoesNotExist $e) {
            return response()->json(['error' => 'No existe el permiso "Generar Informes"'], 403);
        }
    }

    public function importarGeometria(Request $request) 
    {   
        try {
            if (Auth::user()->can('Importar Geometrias')) {
                $cod_provincia = $request->input('cod_provincia');
                $this->tempGeometrias = session('tempGeometrias');

                $provincia = Provincia::where('codigo', $cod_provincia)->first();
                $geomFeature = json_encode($this->tempGeometrias[$cod_provincia]);
                $id_new_geom = $provincia->setGeometriaAttribute($geomFeature);
                if ($id_new_geom !== null) {
                    $nuevo_estado = $this::chequearEstadoGeometrias($provincia, null, $geomFeature);
                    return response()->json(['statusCode'=> 200, 'estado_geom' => $nuevo_estado, 'message' => "Geometría importada correctamente. ID de nueva geometría: ".$id_new_geom]); //mostrar id de geometría?
                } else {
                    return response()->json(['statusCode'=> 500, 'message' => "Error al importar la geometría."]);
                }
            } else {
                return response()->json(['statusCode'=> 403, 'message' => "No tienes permiso para hacer eso."]);
            }
        } catch (PermissionDoesNotExist $e) {
            flash('message', 'No existe el permiso "Importar Geometrias"')->error();
        }
    }

    public function repetirInforme($informe)
    {
        try {
            if (Auth::user()->can('Generar Informes')) {
                $informe = Informe::findOrFail($informe);
                $geoservicio = $informe->geoservicio;
                if ($geoservicio == null) {
                    $geoservicio = new Geoservicio([ //distinto de Geoservicio::create, en este caso NO se almacena en la BD, es una conexión rápida
                        'nombre' => $informe->geoservicio_nombre,
                        'descripcion' => $informe->geoservicio_descripcion,
                        'url' => $informe->geoservicio_url
                    ]);
                } 
                return $this->obtenerResultados($geoservicio, $informe->cod, $informe->nom, $informe->capa);
            } else {
                flash("No tienes permiso para hacer eso.")->error();
                return back();
            }
        } catch (PermissionDoesNotExist $e) {
            flash('message', 'No existe el permiso "Generar Informes"')->error();
        }
    }
}
