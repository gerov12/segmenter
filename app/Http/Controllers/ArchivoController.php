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

        // necesario ya que del lado de la vista solo puedo recorrer los visibles
        $count_archivos_repetidos = $archivos->filter(function ($archivo) {
            return $archivo->id != $archivo->original->id;
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
                    if($data->id != $data->original->id){
                        $unico = false;
                        Log::warning($data->nombre_original." es copia!");
                        $info .= '<span class="badge badge-pill badge-warning"><span class="bi bi-copy" style="font-size: 0.8rem; color: rgb(0, 0, 0);"> Copia</span></span><br>';
                    } else if ($data->copias_count > 1) {
                        $unico = false;
                        Log::info($data->nombre_original." es el archivo original! (Tiene ".$data->numCopias." copias)");
                        $info .= '<button class="badge badge-pill badge-warning" data-toggle="modal" data-archivo="'.$data->id.'" data-name="'.$data->nombre_original.'" data-target="#copiasModal"><span class="bi bi-copy" style="font-size: 0.8rem; color: rgb(0, 0, 0);"> Copiado ('.$data->numCopias.')</span></button><br>';
                    } else {
                        Log::info($data->nombre_original." es el archivo original!");
                    }
                    if ($data->checksum_control == null){
                        $checksumCalculado = false;
                        Log::warning($data->nombre_original. " Checksum no calculado con el nuevo método!");
                        $info .= '<button class="badge badge-pill badge-checksum" data-toggle="modal" data-name="' . $data->nombre_original . '" data-file="' . $data->id . '" data-status="no_check" data-recalculable="' . $owned . '" data-target="#checksumModal"><span class="bi bi-exclamation-triangle" style="font-size: 0.8rem; color: rgb(0, 0, 0);"> Checksum no calculado</span></button><br>';
                    } else if (!$data->checksumOk) {
                        $checksumCorrecto = false;
                        if ($data->checksumObsoleto) {
                            Log::error($data->nombre_original.' checksum obsoleto!');
                            $info .= '<button class="badge badge-pill badge-danger" data-toggle="modal" data-name="' . $data->nombre_original . '" data-file="' . $data->id . '" data-status="old_check" data-recalculable="' . $owned . '" data-target="#checksumModal"><span class="bi bi-calendar-x" style="font-size: 0.8rem; color: rgb(255, 255, 255);"> Checksum obsoleto</span></button><br>';
                        } else {
                            Log::error($data->nombre_original.' error en el checksum!');
                            $info .= '<button class="badge badge-pill badge-danger" data-toggle="modal" data-name="' . $data->nombre_original . '" data-file="' . $data->id . '" data-status="wrong_check" data-recalculable="' . $owned . '" data-target="#checksumModal"><span class="bi bi-x-circle" style="font-size: 0.8rem; color: rgb(255, 255, 255);"> Error de checksum</span></button><br>';
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
            'data'=>$archivos, 
            'count_archivos_repetidos' => $count_archivos_repetidos,
            'count_null_checksums' => $count_null_checksums,
            'count_error_checksums' => $count_error_checksums,
            'count_old_checksums' => $count_old_checksums
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
                    if ( $archivo->esCopia ){
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

    //no envio los erroneos directamente desde la vista para permitir acceder a la función directamente por URL sin pasar por el listado
    public function recalcular_checksums($archivo_id = null){

        try {
            if (Auth::user()->can(['Administrar Archivos', 'Ver Archivos'])){
                //si envié un archivo calculo ese
                if ($archivo_id) {
                    $archivo = Archivo::findOrFail($archivo_id);
                    $archivo->checksumRecalculate();
                    flash('Checksum recalculado para el archivo ' . $archivo->nombre_original)->info();
                } else {
                    $archivos = self::retrieveFiles($user)->filter(function ($archivo) {
                        return !$archivo->checksumOk and !$archivo->checksumObsoleto;
                    });
                    $recalculados = 0;
                    foreach ($archivos as $archivo){
                        $archivo->checksumRecalculate();
                        $recalculados++;
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

    //no envio los obsoletos directamente desde la vista para permitir acceder a la función directamente por URL sin pasar por el listado
    public function sincronizar_checksums($archivo_id = null){

        flash('Función aún en testeo...')->warning()->important();
        return redirect('archivos');
        //Aún falta testeo

        try {
            if (Auth::user()->can(['Administrar Archivos', 'Ver Archivos'])){
                //si envié un archivo calculo ese
                if ($archivo_id) {
                    $archivo = Archivo::findOrFail($archivo_id);
                    $archivo->checksumSync();
                    flash('Checksum sincronizado para el archivo ' . $archivo->nombre_original)->info();
                } else {
                    $archivos = self::retrieveFiles($user)->filter(function ($archivo) {
                        return !$archivo->checksumOk and $archivo->checksumObsoleto;
                    });
                    $sincronizados = 0;
                    foreach ($archivos as $archivo){
                        $archivo->checksumSync();
                        $sincronizados++;
                    }
                    flash($sincronizados . " checksums sincronizados.")->info();
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
        $archivos = self::retrieveFiles($user)->filter(function ($archivo) {
            return $archivo->id != $archivo->original->id;
        });
        $repetidos = [];
        foreach ($archivos as $archivo){
            $original = $archivo->original;
            $repetidos[] = [$original,$archivo];
        }
        return view('archivo.repetidos', compact('repetidos'));
    }

    public function listar_checksums_no_calculados(){
        $user = Auth::user();
        $archivos = self::retrieveFiles($user);
        $checksums_no_calculados = $archivos->filter(function ($archivo) {
            return !$archivo->checksum_control !== null;
        });
        return view('archivo.checksums_no_calculados', compact('checksums_no_calculados'));
    }

    public function listar_checksums_obsoletos(){
        $user = Auth::user();
        $archivos = self::retrieveFiles($user);
        $checksums_calculados = $archivos->filter(function ($archivo) {
            return $archivo->checksum_control !== null;
        });
        $checksums_obsoletos = $checksums_calculados->filter(function ($archivo) {
            return !$archivo->checksumOk and $archivo->checksumObsoleto;
        })->map(function ($archivo) {
            return [
                'archivo' => $archivo,
                'control' => $archivo->checksum_control
            ];
        });
        return view('archivo.checksums_obsoletos', compact('checksums_obsoletos'));
    }

    public function listar_checksums_erroneos(){
        $user = Auth::user();
        $archivos = self::retrieveFiles($user);
        $checksums_calculados = $archivos->filter(function ($archivo) {
            return $archivo->checksum_control !== null;
        });
        $checksums_erroneos = $checksums_calculados->filter(function ($archivo) {
            return !$archivo->checksumOk and !$archivo->checksumObsoleto;
        })->map(function ($archivo) {
            return [
                'archivo' => $archivo,
                'control' => $archivo->checksum_control
            ];
        });
        return view('archivo.checksums_erroneos', compact('checksums_erroneos'));
    }
}
