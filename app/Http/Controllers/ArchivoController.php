<?php

namespace App\Http\Controllers;

use App\Model\Archivo;
use Illuminate\Http\Request;
use DataTables;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
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

        // necesario ya que del lado de la vita solo puedo recorrer los visibles
        $count_archivos_repetidos = $archivos->filter(function ($archivo) {
            return $archivo->esCopia;
        })->count();
    
        $count_null_checksums = $count_error_checksums = 0;
        
        foreach ($archivos as $archivo) {
            if (!$archivo->checksum_control()->exists()){
                $count_null_checksums++;
            } else if (!$archivo->checkChecksum) {
                $count_error_checksums++;
            }
        }

        if ($request->ajax()) {
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
                    if($data->es_copia){
                        $unico = false;
                        Log::warning($data->nombre_original." es copia!");
                        $info .= '<span class="badge badge-pill badge-warning"><span class="bi bi-copy" style="font-size: 0.8rem; color: rgb(0, 0, 0);"> Copia</span></span><br>';
                    } else {
                        Log::info($data->nombre_original." es el archivo original!");
                    }
                    if (!$data->checksum_control()->exists()){
                        $checksumCalculado = false;
                        Log::warning($data->nombre_original. " Checksum no calculado con el nuevo método!");
                        $info .= '<button class="badge badge-pill badge-checksum" data-toggle="modal" data-name="' . $data->nombre_original . '" data-file="' . $data->id . '" data-status="no_check" data-recalculable="' . $owned . '" data-target="#checksumModal"><span class="bi bi-exclamation-triangle" style="font-size: 0.8rem; color: rgb(0, 0, 0);"> Checksum no calculado</span></button><br>';
                    } else if (!$data->checkChecksum) {
                        $checksumCorrecto = false;
                        Log::error($data->nombre_original.' error en el checksum!');
                        $info .= '<button class="badge badge-pill badge-danger" data-toggle="modal" data-name="' . $data->nombre_original . '" data-file="' . $data->id . '" data-status="old_check" data-recalculable="' . $owned . '" data-target="#checksumModal"><span class="bi bi-x-circle" style="font-size: 0.8rem; color: rgb(255, 255, 255);"> Error de checksum</span></button><br>';
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
            'data'=>$archivos, 
            'count_archivos_repetidos' => $count_archivos_repetidos,
            'count_null_checksums' => $count_null_checksums,
            'count_error_checksums' => $count_error_checksums
        ]);
    }

    private static function retrieveFiles($user){
        $archivos = $user->visible_files()->withCount('viewers')->with(['user','checksum_control'])->get();
        $archivos = $archivos->merge($user->mis_files()->withCount('viewers')->with(['user','checksum_control'])->get());
        try {
            if ($user->can('Ver Archivos')) {
                $archivos = $archivos->merge(Archivo::withCount('viewers')->with(['user','checksum_control'])->get());
            }
        } catch (PermissionDoesNotExist $e) {
            Session::flash('message', 'No existe el permiso "Ver Archivos"');
        }
        return $archivos;
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
      $result = $archivo->load('user');
      if ($request->format == 'html') {
        $result = $result->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
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
        return view('archivo.list');
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
      return view('archivo.list');
    }

    //no envio los repetidos directamente desde la vista para permitir acceder a la función directamente por URL sin pasar por el listado
    public function eliminar_repetidos() {


        flash('Función aún en testeo...')->warning()->important();
        return redirect('archivos');
        //Aún falta testeo

        $this->middleware('can:run-setup');

        try {
            if (Auth::user()->can(['Administrar Archivos', 'Ver Archivos'])){
                // Para todos los archivos
                $archivos = Archivo::all();
                $eliminados = 0;
                Log::error("------------- ELIMINAR ARCHIVOS REPETIDOS -----------------------------");
                foreach ($archivos as $archivo){
                    if ( $archivo->es_copia ){
                        // Archivo repetido
                        $original = Archivo::where('checksum',$archivo->checksum)->orderby('id','asc')->first();
                        // Logs repetidos pero es necesario ya que este log debe mostrarse antes que los de limpiar_copia()
                        Log::error("Archivo " . $archivo->id . ". Checksum: " . $archivo->checksum.". Copia de archivo id: ".$original->id."." );
                        $archivo->limpiar_copia($original);
                        $eliminados = $eliminados + 1;
                    } else {
                        Log::info("Archivo " . $archivo->id . ". Checksum: " . $archivo->checksum.". Es el archivo original." );
                    }
                }
                flash($eliminados . " archivos eliminados de ".$archivos->count()." encontrados.")->info();
                return redirect('archivos');
            } else {
                flash('message', 'No tienes permiso para hacer eso.')->error();
                return back();
            }
        } catch (PermissionDoesNotExist $e) {
            flash('message', 'No existe el permiso "Administrar Archivos" o "Ver Archivos"')->error();
        }
    }

    //no envio los obsoletos directamente desde la vista para permitir acceder a la función directamente por URL sin pasar por el listado
    public function recalcular_checksums($archivo_id = null){

        flash('Función aún en testeo...')->warning()->important();
        return redirect('archivos');
        //Aún falta testeo

        try {
            if (Auth::user()->can(['Administrar Archivos', 'Ver Archivos'])){
                //si envié un archivo calculo ese
                if ($archivo_id) {
                    $archivo = Archivo::findOrFail($archivo_id);
                    $archivo->checksumRecalculate();
                    flash('Checksum recalculado para el archivo ' . $archivo->nombre_original)->info();
                } else {
                    $archivos = Archivo::all(); //modificar para traer solo los obsoletos con un scope
                    $recalculados = 0;
                    foreach ($archivos as $archivo){
                        if (!$archivo->checkChecksum){ //si uso el scope esto no debería chequearse
                            // Archivo con checksum viejo
                            $archivo->checksumRecalculate();
                            $recalculados++;
                        }
                    }
                    flash($recalculados . " checksums recalculados.")->info();
                }
                return redirect('archivos');
            } else {
                flash('No tienes permiso para hacer eso.')->error();
                return back();
            }
        } catch (PermissionDoesNotExist $e) {
            flash('No existe el permiso "Administrar Archivos" o "Ver Archivos"')->error();
        }
    }

    public function listar_repetidos(){
        $user = Auth::user();
        $archivos = self::retrieveFiles($user);
        $repetidos = [];
        foreach ($archivos as $archivo){
            if ( $archivo->esCopia ){
                // Archivo repetido
                $original = Archivo::where('checksum',$archivo->checksum)->orderby('id','asc')->first();
                $repetidos[] = [$original,$archivo];
            } else {
                $mensaje = "Es el archivo original.";
            }
        }
        return view('archivo.repetidos', compact('repetidos'));
    }

    public function listar_checksums_no_calculados(){
        $user = Auth::user();
        $archivos = self::retrieveFiles($user);
        $checksums_no_calculados = $archivos->filter(function ($archivo) {
            return !$archivo->checksum_control()->exists();
        });
        return view('archivo.checksums_no_calculados', compact('checksums_no_calculados'));
    }

    public function listar_checksums_obsoletos(){
        $user = Auth::user();
        $archivos = self::retrieveFiles($user);
        $checksums_calculados = $archivos->filter(function ($archivo) {
            return $archivo->checksum_control()->exists();
        });
        $checksums_obsoletos = $checksums_calculados->filter(function ($archivo) {
            return !$archivo->checkChecksum;
        });
        return view('archivo.checksums_obsoletos', compact('checksums_obsoletos'));
    }
}
