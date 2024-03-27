<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Entidad;
use App\Model\Archivo;
use App\Model\Provincia;
use App\Model\Geometria;
use Auth;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Illuminate\Support\Facades\Log;

class EntidadController extends Controller
{
    /**
     * Mostrar la Entidad
     */
    public function show($entidad) //: View
    {
        return view('entidad.view', [
            'entidad' => Entidad::findOrNew($entidad)
            ,'provincia' => $entidad->provincia ?? new Provincia (['nombre'=>'No province','id'=>0,'codigo'=>0])
            ,'svg' => $entidad->geometria ?? new Geometria([])
        ]);
    }

    public function index()
    {
        $entidades= '';//Entidad::with(['localidad','localidad.departamentos','localidad.departamentos.provincia'])->get();
        return view('entidad.list',['entidades' => $entidades]);
    }

    public function store(Request $request)
    {
      if (! Auth::check()) {
          $mensaje = 'No tiene permiso para cargar Entidades o no esta logueado';
          flash($mensaje)->error()->important();
          return $mensaje;
      }

      $AppUser = Auth::user();
      flash('TODO: Funcion en desarrollo')->warning()->important();

      // Carga de arcos o e00
    if ($request->hasFile('shp')) {
      if($shp_file = Archivo::cargar($request->shp, Auth::user(),
        'shape', [$request->shx, $request->dbf, $request->prj])) {
        flash("Archivo de geodatos SHP/E00. Identificado: ".$shp_file->tipo)->info();
      } else {
        flash("Error en el modelo cargar archivo al registrar SHP/E00")->error();
      }
      //$shp_file->epsg_def = $epsg_id;
      $shp_file->save();
      $shp_file->procesar();
      $shp_file->pasarData();

    }
      return view('entidad.cargar');
    }
    public function cargar(Request $request)
    {
      return view('entidad.cargar');
    }

    public function entsList()
    {
           // Ents, pastores e árboles :D
           $aEnts=[];
           $entsQuery = Entidad::with(['localidad','localidad.departamentos',
                                                'localidad.departamentos.provincia']);
           $codigo = (!empty($_GET["codigo"])) ? ($_GET["codigo"]) : ('');
           if ($codigo!='') {
              $provsQuery->where('codigo', '=', $codigo);
           }
    	  $qEnts = $entsQuery
/*                ->withCount(['departamentos','fracciones'])
                ->with('departamentos')
                ->with('fracciones')
                ->with('fracciones.radios')
                ->with('fracciones.radios.tipo')
                ->with('departamentos.localidades') */
                //->get(['codigo','nombre'])
                ->orderBy('codigo','asc') //'nombre'])
                ->get();
//        dd($provs->get());
        foreach ($qEnts as $ent){
//dd($ent->localidad);
          $aEnts[$ent->codigo]=['id' => $ent->id,'codigo' => $ent->codigo,
                                'nombre' => $ent->nombre,
                                'localidad' => $ent->localidad->nombre,
                                'departamento' => $ent->localidad->departamentos->first()->nombre,
                                'provincia' => $ent->localidad->departamentos->first()->provincia->nombre,
                                'codprov'=> $ent->localidad->departamentos->first()->provincia->codigo];
        }
      return datatables()->of($aEnts)
                ->addColumn('action', function($data){
                    $button = '<button type="button" class="btn_descarga btn-sm btn-primary" > Descargar </button> ';
                    // botón de eliminar Entidad  en test, si esta logueado.
                    if (Auth::check()) {
                            try {
                                if ( ( Auth::user()->hasPermissionTo($data['codprov'], 'filters') and Auth::user()->can('Borrar Entidad') ) )
                                // Botón borrar sólo si tiene permiso y la Entiadd pertenece a la provincia (TODO).
                                {
                                    $button .= '<button type="button" class="btn_ent_delete btn-sm btn-danger "> Borrar </button>';
                                }
                            } catch (PermissionDoesNotExist $e) {
                            Log::warning('No existe el permiso '.$e->getMessage(),[$data]);
                            }
                            return $button;
                        }
                })
            ->make(true);
    }

}
