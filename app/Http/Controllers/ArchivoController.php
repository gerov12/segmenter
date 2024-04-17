<?php

namespace App\Http\Controllers;

use App\Model\Archivo;
use Illuminate\Http\Request;
use DataTables;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Auth;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Session;

class ArchivoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {   
        $user = Auth::user();
        $archivos = self::retrieveFiles($user);
        $count_archivos = $archivos->count();
        if (!$request->ajax()) {
            // necesario ya que del lado de la vista solo puedo recorrer los visibles
            $count_estados = self::countEstados($archivos);
        } else {
            return Datatables::of($archivos)
                ->addIndexColumn()
                ->addColumn('created_at_h', function ($row){
                        return $row->created_at->format('d-M-Y');})
                ->addColumn('usuario', function ($row){
                        return $row->user->name;})
                ->addColumn('size_h', function ($row, $precision = 1 ){
                        $size = $row->size;
                        if ( $size > 0 ) {
                        $size = (int) $size;
                        $base = log($size) / log(1024);
                        $suffixes = array(' bytes', ' KB', ' MB', ' GB', ' TB');
                        return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
                        }
                        return $size;
                        })
                ->addColumn('status', function($data) use ($user){
                    $info = '';
                    $unico = $checksumCalculado = $checksumCorrecto = $storageOk = true;
                    $owned = ($data->ownedByUser($user) || $user->can('Administrar Archivos', 'Ver Archivos')) ? true : false;
                    if($data->id != $data->original->id){
                        $unico = false;
                        Log::warning($data->nombre_original." es copia!");
                        $info .= '<button id="btn-ver-original" class="badge badge-pill badge-warning" data-info="false" data-archivo="'.$data->id.'" data-name="'.$data->nombre_original.'" data-limpiable="' . $owned . '" data-owner="' . $data->user->name . '"><span class="bi bi-copy" style="font-size: 0.8rem; color: rgb(0, 0, 0);"> Copia</span></button><br>';
                    } else if ($data->copias_count > 1) {
                        $unico = false;
                        Log::info($data->nombre_original." es el archivo original! (Tiene ".$data->numCopias." copias)");
                        $info .= '<span class="badge badge-pill badge-primary"><span class="bi bi-file-earmark-check" style="font-size: 0.8rem; color: rgb(255, 255, 255);"> Original </span></span><br>';
                        $info .= '<button id="btn-ver-copias" class="badge badge-pill badge-warning" data-info="false" data-archivo="'.$data->id.'" data-name="'.$data->nombre_original.'" data-limpiables="' . $owned . '"><span class="bi bi-copy" style="font-size: 0.8rem; color: rgb(0, 0, 0);"> Ver copias ('.$data->numCopias.')</span></button><br>';
                    } else {
                        Log::info($data->nombre_original." es el archivo original!");
                    }
                    if ($data->checksum_control == null){
                        $checksumCalculado = false;
                        Log::warning($data->nombre_original. " Checksum no calculado con el nuevo método!");
                        $info .= '<button id="btn-checksum" class="badge badge-pill badge-checksum" data-info="false" data-name="' . $data->nombre_original . '" data-file="' . $data->id . '" data-status="no_check" data-recalculable="' . $owned . '"><span class="bi bi-exclamation-triangle" style="font-size: 0.8rem; color: rgb(0, 0, 0);"> Checksum no calculado</span></button><br>';
                    } else if (!$data->checksumOk) {
                        $checksumCorrecto = false;
                        if ($data->checksumObsoleto) {
                            Log::error($data->nombre_original.' checksum obsoleto!');
                            $info .= '<button id="btn-checksum" class="badge badge-pill badge-danger" data-info="false" data-name="' . $data->nombre_original . '" data-file="' . $data->id . '" data-status="old_check" data-recalculable="' . $owned . '"><span class="bi bi-calendar-x" style="font-size: 0.8rem; color: rgb(255, 255, 255);"> Checksum obsoleto</span></button><br>';
                        } else {
                            Log::error($data->nombre_original.' error en el checksum!');
                            $info .= '<button id="btn-checksum" class="badge badge-pill badge-danger" data-info="false" data-name="' . $data->nombre_original . '" data-file="' . $data->id . '" data-status="wrong_check" data-recalculable="' . $owned . '"><span class="bi bi-x-circle" style="font-size: 0.8rem; color: rgb(255, 255, 255);"> Error de checksum</span></button><br>';
                        }
                    } else {
                        Log::info($data->nombre_original.' checksum ok!');
                    }
                    if (!$data->checkStorage()){
                        $storageOk = false;       
                        $info .= '<span class="badge badge-pill badge-dark"><span class="bi bi-archive" style="font-size: 0.8rem; color: rgb(255, 255, 255);"> Problema de storage</span></span><br>';
                    }
                    if ($unico and $checksumCalculado and $checksumCorrecto and $storageOk){
                        $info .= '<span class="badge badge-pill badge-success"><span class="bi bi-check" style="font-size: 0.8rem; color: rgb(255, 255, 255);"> OK</span></span><br>';
                    }
                    return $info;
                })
                ->addColumn('action', function($data){
                    $button = '<button type="button" class="btn_descarga btn-sm btn-primary" > Descargar </button> ';
                    $button .= '<button type="button" class="btn_arch btn-sm btn-primary" > Ver </button>';
                    $button .= '<button type="button" class="btn_arch_procesar btn-sm btn-secondary" > ReProcesar </button>';
                    $button .= '<button type="button" class="btn_arch_pasar btn-sm btn-secondary" > Pasar Data </button>';

                    /*
                    Sin botón de eliminar archivo por el momento

                    if ($data->user_id == Auth::user()->id) {
                        $button .= '<button type="button" class="btn_arch_delete btn-sm btn-danger " > Borrar </button>';
                    } else {
                        try {
                            if (Auth::user()->hasPermissionTo('Administrar Archivos')){
                                $button .= '<button type="button" class="btn_arch_delete btn-sm btn-danger " > Borrar </button>';
                            } else if (Auth::user()->visible_files()->get()->contains($data)){
                                $button .= '<button type="button" class="btn_arch_detach btn-sm btn-danger " > Dejar de ver </button>';
                            }
                        } catch (PermissionDoesNotExist $e) {
                            Log::error('No existe el permiso "Administrar Archivos"');
                        }
                    }
                    */
                    return $button;
                })
                ->rawColumns(['status','action'])
                ->setTotalRecords($count_archivos)
                ->make(true);
        }

        return view('archivo.list')->with([
            'data' => $archivos, 
            'count_archivos_repetidos' => $count_estados["repetidos"],
            'count_null_checksums' => $count_estados["null"],
            'count_error_checksums' => $count_estados["error"],
            'count_old_checksums' => $count_estados["old"]
        ]);
    }

    public function getCopias(Archivo $archivo) {
        $copias = $archivo->copias()->with('user')->where('id', '!=' , $archivo->id)->get();
        $copias->map(function ($copia) {
            $copia->fecha = $copia->created_at->format('d-M-Y');
            return $copia;
        });
        return response()->json($copias);
    }

    public function getOriginal(Archivo $archivo) {
        $original = $archivo->original()->with('user')->first();
        $original->fecha = $original->created_at->format('d-M-Y');
        return response()->json($original);
    }

    private static function retrieveFiles($user){
        $archivos = $user->visible_files()->withCount(['viewers','copias'])->with(['user','checksum_control','original', 'original.user'])->get();
        $archivos = $archivos->merge($user->mis_files()->withCount(['viewers','copias'])->with(['user','checksum_control','original', 'original.user'])->get());
        try {
            if ($user->can('Ver Archivos')) {
                $archivos = $archivos->merge(Archivo::withCount(['viewers','copias'])->with(['user','checksum_control','original', 'original.user'])->get());
            }
        } catch (PermissionDoesNotExist $e) {
            Session::flash('message', 'No existe el permiso "Ver Archivos"');
        }
        return $archivos;
    }

    private static function countEstados($archivos){
        $count_archivos_repetidos = $archivos->filter(function ($archivo) {
            return $archivo->esCopia;
        })->count();

        $count_null_checksums = $archivos->filter(function ($archivo) {
            return $archivo->checksum_control == null;
        })->count();

        $controlled_checksums = $archivos->reject(function ($archivo) { 
            return $archivo->checksum_control == null; // que tengan checksum_control
        });

        $wrong_checksums = $controlled_checksums->filter(function ($archivo) { //filtro unicamente por los que tienen checksum_control
            return !$archivo->checksumOk; //que tengan checksum erroneo
        });

        $count_error_checksums = $wrong_checksums->filter(function ($archivo) { //filtro unicamente por los que tienen checksums erroneos
            return !$archivo->checksumObsoleto; //que no sean obsoletos
        })->count();

        $count_old_checksums = $wrong_checksums->filter(function ($archivo) { //filtro unicamente por los que tienen checksums erroneos
            return !$archivo->checksumOk and $archivo->checksumObsoleto; //que sean obsoletos
        })->count();

        return ["repetidos"=>$count_archivos_repetidos, "null"=>$count_null_checksums, "error"=>$count_error_checksums, "old"=>$count_old_checksums];
    }

    public function updateCounts(){
        $user = Auth::user();
        $archivos = self::retrieveFiles($user);
        $count_estados = self::countEstados($archivos);
        return response()->json($count_estados);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Model\Archivo  $archivo
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Archivo $archivo)
    {
    	//
///      return response($request->format);
      $result = Archivo::withCount(['viewers','copias'])->with(['user','checksum_control','original', 'original.user'])->findOrFail($archivo->id);
      if ($request->format == 'html') {
        $size = $result->size;
        if ( $size > 0 ) {
        $size = (int) $size;
        $base = log($size) / log(1024);
        $suffixes = array(' bytes', ' KB', ' MB', ' GB', ' TB');
        $result->tamaño = round(pow(1024, $base - floor($base)), 1) . $suffixes[floor($base)];
        }
        return view('archivo.info')->with(['archivo'=>$result]);
      }
     	return $result;

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Model\Archivo  $archivo
     * @return \Illuminate\Http\Response
     */
    public function edit(Archivo $archivo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Model\Archivo  $archivo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Archivo $archivo)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Model\Archivo  $archivo
     * @return \Illuminate\Http\Response
     */
    public function destroy(Archivo $archivo)
    {


        flash('Función no implementada x seguridad...')->warning()->important();
        //return view('archivo.list');
        //Aún falta testeo

        $this->middleware('can:run-setup');
	    // Borro el archivo del storage
	    //
        $vistas = DB::table('file_viewer')->where('archivo_id', $archivo->id)->count();
        if ($vistas == 0){
            $file= $archivo->nombre;
            if(Storage::delete($file)){
                Log::info('Se borró el archivo: '.$archivo->nombre_original);
            }else{
                Log::error('NO se borró el archivo: '.$file);
            }
            $archivo->delete();
            return 'ok';
        } else {
            return $vistas;
        }

    }

    public function detach(Archivo $archivo)
    {
	    // Borro el archivo del storage
	    //
      //  Auth::user()->visible_files()->detach($archivo->id);
        return false;
    }

    /**
     * Descargar archivo resource.
     *
     * @param  \App\Model\Archivo  $archivo
     * @return \Illuminate\Http\Response
     */
    public function descargar(Archivo $archivo)
    {
	    // Descarga de archivo.
	    return $archivo->descargar();
    }

    /**
     * Procesar archivo resource.
     *
     * @param  \App\Model\Archivo  $archivo
     * @return \Illuminate\Http\Response
     */
    public function procesar(Archivo $archivo)
    {
	    // Mensaje extraño
	    $mensaje = $archivo->procesar()?'ik0':'m4l';

        $user = Auth::user();
        $archivos = self::retrieveFiles($user);
        $count_estados = self::countEstados($archivos);
        return view('archivo.list')->with([
            'data' => $archivos, 
            'count_archivos_repetidos' => $count_estados["repetidos"],
            'count_null_checksums' => $count_estados["null"],
            'count_error_checksums' => $count_estados["error"],
            'count_old_checksums' => $count_estados["old"]
        ]);
    }

    /**
     * Pasar Data de archivo resource.
     *
     * @param  \App\Model\Archivo  $archivo
     * @return \Illuminate\Http\Response
     */
    public function pasarData(Archivo $archivo)
    {
	    // Mensaje extraño
	    $mensaje = $archivo->pasarData()?'ik0':'m4l';
        $user = Auth::user();
        $archivos = self::retrieveFiles($user);
        $count_estados = self::countEstados($archivos);
        return view('archivo.list')->with([
            'data' => $archivos, 
            'count_archivos_repetidos' => $count_estados["repetidos"],
            'count_null_checksums' => $count_estados["null"],
            'count_error_checksums' => $count_estados["error"],
            'count_old_checksums' => $count_estados["old"]
        ]);
    }
    
    //no envio los repetidos directamente desde la vista para permitir acceder a la función directamente por URL sin pasar por el listado
    public function eliminar_repetidos(Request $request, $archivo_id = null) {

        //flash('Función aún en testeo...')->warning()->important();
        //return redirect('archivos');
        //Aún falta testeo

        $this->middleware('can:run-setup');

        try {
            $user = Auth::user();
            $tipo = $request->get('type');
            if ($archivo_id) {
                if($tipo == "individual") { //copia
                    $archivo = Archivo::findOrFail($archivo_id);
                    if (($archivo->ownedByUser($user) || $user->can('Administrar Archivos', 'Ver Archivos'))) {
                        $archivo->limpiarCopia();
                        $respuesta = ['statusCode'=> 200,'message' => 'Se eliminó la copia ' . $archivo->nombre_original];
                    } else {
                        $respuesta = ['statusCode'=> 403,'message' => 'No tienes permiso para hacer eso.'];
                    } 
                } else if ($tipo == "bulk") { //bulk de copias de un original
                    $archivo = Archivo::findOrFail($archivo_id);
                    if (($archivo->ownedByUser($user) || $user->can('Administrar Archivos', 'Ver Archivos'))) {
                        $eliminados = 0;
                        $copias = $archivo->copias()->with('user')->where('id', '!=' , $archivo->id)->get();
                        $id_copias = $copias->map(function($copia) {
                            return $copia->id;
                        });
                        foreach ($copias as $archivo){
                            $archivo->limpiarCopia();
                            $eliminados++;
                        }
                        $respuesta = ['statusCode'=> 200, 'id_copias' => $id_copias, 'message' => "Se limpiaron ". $eliminados . " copias de " . $archivo->nombre_original . "."];
                    } else {
                        $respuesta = ['statusCode'=> 403,'message' => 'No tienes permiso para hacer eso.'];
                    }
                }
                return response()->json($respuesta);
            } else if (!$archivo_id && $tipo="bulk"){ //bulk total
                $archivos = self::retrieveFiles($user)->filter(function ($archivo) {
                    return $archivo->esCopia;
                });
                if (!$user->can(['Administrar Archivos', 'Ver Archivos'])){
                    $archivos = $archivos->filter(function ($archivo) use ($user) {
                        return $archivo->ownedByUser($user); // si no tengo permisos me quedo solo con los míos
                    });
                }
                $eliminados = 0;
                foreach ($archivos as $archivo){
                    $archivo->limpiarCopia();
                    $eliminados++;
                }
                $respuesta = ['statusCode'=> 200,'message' => $eliminados . " archivos copia limpiados."];
                return response()->json($respuesta);
            }
        } catch (PermissionDoesNotExist $e) {
            flash('message', 'No existe el permiso "Administrar Archivos" o "Ver Archivos"')->error();
        }
    }

    //no envio los erroneos directamente desde la vista para permitir acceder a la función directamente por URL sin pasar por el listado
    public function recalcular_checksums(Request $request, $archivo_id = null){

        //flash('Función aún en testeo...')->warning()->important();
        //return redirect('archivos');
        //Aún falta testeo

        try {
            $user = Auth::user();
            $tipo = $request->get('type');
            //si envié un archivo calculo ese
            if ($archivo_id && $tipo="individual") {
                $archivo = Archivo::findOrFail($archivo_id);
                if (($archivo->ownedByUser($user) || $user->can('Administrar Archivos', 'Ver Archivos'))) {
                    $archivo->checksumRecalculate();
                    $respuesta = ['statusCode'=> 200,'message' => 'Checksum recalculado para el archivo ' . $archivo->nombre_original];
                } else {
                    $respuesta = ['statusCode'=> 403,'message' => 'No tienes permiso para hacer eso.'];
                }
                return response()->json($respuesta);
            } else if (!$archivo_id && $tipo="bulk"){
                $estado = $request->get('status');
                $todos = self::retrieveFiles($user);
                if ($estado == "no_calculados"){
                    $archivos = $todos->filter(function ($archivo) {
                        return $archivo->checksum_control === null;
                    });
                } else if ($estado == "erroneos"){
                    $checksums_calculados = $todos->filter(function ($archivo) {
                        return $archivo->checksum_control !== null;
                    });
                    $archivos = $checksums_calculados->filter(function ($archivo) {
                        return !$archivo->checksumOk and !$archivo->checksumObsoleto;
                    });
                }
                if (!$user->can(['Administrar Archivos', 'Ver Archivos'])){
                    $archivos = $archivos->filter(function ($archivo) use ($user) {
                        return $archivo->ownedByUser($user); // si no tengo permisos me quedo solo con los míos
                    });
                }
                $recalculados = 0;
                foreach ($archivos as $archivo){
                        $archivo->checksumRecalculate();
                        $recalculados++;
                }
                $respuesta = ['statusCode'=> 200,'message' => $recalculados . " checksums recalculados."];
                return response()->json($respuesta);
            }
        } catch (PermissionDoesNotExist $e) {
            flash('No existe el permiso "Administrar Archivos" o "Ver Archivos"')->error();
        }
    }

    //no envio los obsoletos directamente desde la vista para permitir acceder a la función directamente por URL sin pasar por el listado
    public function sincronizar_checksums(Request $request, $archivo_id = null){

        //flash('Función aún en testeo...')->warning()->important();
        //return redirect('archivos');
        //Aún falta testeo

        try {
            $user = Auth::user();
            $tipo = $request->get('type');
            //si envié un archivo sincronizo ese
            if ($archivo_id && $tipo="individual") {
                $archivo = Archivo::findOrFail($archivo_id);
                if (($archivo->ownedByUser($user) || $user->can('Administrar Archivos', 'Ver Archivos'))) {
                    $archivo->checksumSync();
                    $respuesta = ['statusCode'=> 200,'message' => 'Checksum sincronizado para el archivo ' . $archivo->nombre_original];
                } else {
                    $respuesta = ['statusCode'=> 403,'message' => 'No tienes permiso para hacer eso.'];
                }
                return response()->json($respuesta);
            } else if (!$archivo_id && $tipo="bulk"){
                $todos = self::retrieveFiles($user);
                $checksums_calculados = $todos->filter(function ($archivo) {
                    return $archivo->checksum_control !== null;
                });
                $archivos = $checksums_calculados->filter(function ($archivo) {
                    return !$archivo->checksumOk and $archivo->checksumObsoleto;
                });
                if (!$user->can(['Administrar Archivos', 'Ver Archivos'])){
                    $archivos = $archivos->filter(function ($archivo) use ($user) {
                        return $archivo->ownedByUser($user); // si no tengo permisos me quedo solo con los míos
                    });
                }
                $sincronizados = 0;
                foreach ($archivos as $archivo){
                    $archivo->checksumSync();
                    $sincronizados++;
                }
                $respuesta = ['statusCode'=> 200,'message' => $sincronizados . " checksums sincronizados."];
                return response()->json($respuesta);
            }
        } catch (PermissionDoesNotExist $e) {
            flash('No existe el permiso "Administrar Archivos" o "Ver Archivos"')->error();
        }
    }

    public function listar_repetidos(){
        $user = Auth::user();
        $archivos = self::retrieveFiles($user)->filter(function ($archivo) {
            return $archivo->esCopia;
        });
        $repetidos = [];
        $owned_count = self::count_owned_files($archivos);
        foreach ($archivos as $archivo){
            $original = $archivo->original;
            $repetidos[] = [$original,$archivo];
        }
        return view('archivo.repetidos')->with([
            "repetidos"=>$repetidos,
            "owned"=>$owned_count
        ]);
    }

    public function listar_checksums_no_calculados(){
        $user = Auth::user();
        $archivos = self::retrieveFiles($user);
        $checksums_no_calculados = $archivos->filter(function ($archivo) {
            return $archivo->checksum_control === null;
        });
        $owned_count = self::count_owned_files($checksums_no_calculados);
        return view('archivo.checksums_no_calculados')->with([
            "checksums_no_calculados"=>$checksums_no_calculados,
            "owned"=>$owned_count
        ]);
    }

    public function listar_checksums_obsoletos(){
        $user = Auth::user();
        $archivos = self::retrieveFiles($user);
        $checksums_calculados = $archivos->filter(function ($archivo) {
            return $archivo->checksum_control !== null;
        });
        $checksums_obsoletos = $checksums_calculados->filter(function ($archivo) {
            return !$archivo->checksumOk and $archivo->checksumObsoleto;
        });
        $owned_count = self::count_owned_files($checksums_obsoletos);
        $checksums_obsoletos = $checksums_obsoletos->map(function ($archivo) {
            return [
                'archivo' => $archivo,
                'control' => $archivo->checksum_control
            ];
        });
        return view('archivo.checksums_obsoletos')->with([
            "checksums_obsoletos"=>$checksums_obsoletos,
            "owned"=>$owned_count
        ]);
    }

    public function listar_checksums_erroneos(){
        $user = Auth::user();
        $archivos = self::retrieveFiles($user);
        $checksums_calculados = $archivos->filter(function ($archivo) {
            return $archivo->checksum_control !== null;
        });
        $checksums_erroneos = $checksums_calculados->filter(function ($archivo) {
            return !$archivo->checksumOk and !$archivo->checksumObsoleto;
        });
        $owned_count = self::count_owned_files($checksums_erroneos);
        $checksums_erroneos = $checksums_erroneos->map(function ($archivo) {
            return [
                'archivo' => $archivo,
                'control' => $archivo->checksum_control
            ];
        });
        return view('archivo.checksums_erroneos')->with([
            "checksums_erroneos"=>$checksums_erroneos,
            "owned"=>$owned_count
        ]);
    }

    private static function count_owned_files($archivos){
        $user = Auth::user();
        $owned = $archivos->filter(function ($archivo) use ($user){
            return ($archivo->ownedByUser($user) || $user->can('Administrar Archivos', 'Ver Archivos'));
        })->count();
        return $owned;
    }

    public function update_owned_count(Request $request){
        $user = Auth::user();
        $estado = $request->get('estado');
        switch ($estado) {
            case "repetidos":
                $archivos = self::retrieveFiles($user)->filter(function ($archivo) {
                    return $archivo->esCopia;
                });
                break;
            case "no_calculados":
                $archivos = self::retrieveFiles($user)->filter(function ($archivo) {
                    return $archivo->checksum_control === null;
                });
                break;
            case "obsoletos":
                $all = self::retrieveFiles($user);
                $checksums_calculados = $all->filter(function ($archivo) {
                    return $archivo->checksum_control !== null;
                });
                $archivos = $checksums_calculados->filter(function ($archivo) {
                    return !$archivo->checksumOk and $archivo->checksumObsoleto;
                });
                break;
            case "erroneos":
                $all = self::retrieveFiles($user);
                $checksums_calculados = $all->filter(function ($archivo) {
                    return $archivo->checksum_control !== null;
                });
                $archivos = $checksums_calculados->filter(function ($archivo) {
                    return !$archivo->checksumOk and !$archivo->checksumObsoleto;
                });
                break;
        }
        $count = self::count_owned_files($archivos);
        return response()->json($count);
    }
}
