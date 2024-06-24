<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Operativo;
use App\Model\Provincia;
use Auth;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class OperativoController extends Controller
{
    /**
     * Mostrar el Operativo
     */
    public function show($operativo, Request $request) //: View
    {
      $oOperativo =  Operativo::findOrNew($operativo);
      $aProvincias = $oOperativo->provincias;
      if ($request->ajax()) {
        return view('operativo.info', [
            'operativo' => $oOperativo
            ,'provincias' => $aProvincias
        ]);
      }else{
        return view('operativo.view', [
          'operativo' => $oOperativo
          ,'provincias' => $aProvincias
      ]);
      }
    }

    public function index()
    {
        $operativos= Operativo::all();
        return view('operativo.list',['operativos' => $operativos]);
    }

    public function store(Request $request)
    {
      if (! Auth::check()) {
          $mensaje = 'No tiene permiso para cargar Operativos o no esta logueado';
          flash($mensaje)->error()->important();
          return $mensaje;
      }

      $AppUser = Auth::user();
      flash('TODO: Funcion en desarrollo')->warning()->important();

      return view('operativo.list');
    }


    public function operativosList()
    {
      if (session('operativo')) {
        $operativo_actual = Operativo::hydrate( session('operativo') )->first();
      } else { $operativo_actual = new Operativo; }
          // Opes, Opas inclusivo :D
           $aOpes=[];
           $opesQuery = Operativo::query();
           $codigo = (!empty($_GET["codigo"])) ? ($_GET["codigo"]) : ('');
           if ($codigo!='') {
              $opesQuery->where('codigo', '=', $codigo);
           }
    	  $qOpes = $opesQuery
                ->orderBy('id','asc')
                ->get();
        foreach ($qOpes as $op){
          $aOpes[$op->id]=['id' => $op->id,
                           'nombre' => $op->nombre,
                           'observacion' => $op->observacion,
                           'seleccionado' => $op->id == $operativo_actual->id
                          ];
        }
      return datatables()->of($aOpes)
                          ->addColumn('action', function($data){
                            // botón de seleccionar Operativo
                            if($data['seleccionado']==false) {
                            $button = '<button type="button" class="btn_seleccionar btn-sm btn-primary" > Seleccionar </button> ';
                            } else {
                              $button = '<b>Seleccionado</b>';
                            }
                            return $button;
                        })
            ->make(true);
    }

    /**
     * Seleccionar Operativo
     */
    public function seleccionar(Operativo $operativo, Request $request) //: View
    {
      $oOperativo =  Operativo::findOrNew($operativo);
      $operativo_actual = session::put('operativo', $oOperativo->toArray());
      return view('home');

    }
}
