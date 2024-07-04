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
use Illuminate\Support\Facades\Validator;

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
      try {
        if (Auth::user()->can('Administrar Operativos')) {    
            Validator::make($request->all(), [
                'nombre' => 'required|string|max:255|unique:operativo',
                'observacion' => 'nullable|string|max:255'
            ])->validateWithBag('new');

            $operativo = Operativo::create([
                'nombre' => $request->nombre,
                'observacion' => $request->observacion
            ]);

            flash("Operativo creado!")->success();
            return redirect()->route('operativos');
        } else {
            flash("No tienes permiso para hacer eso.")->error();
            return back();
        }
      } catch (PermissionDoesNotExist $e) {
          flash('message', 'No existe el permiso "Administrar Geoservicios"')->error();
      }
    }

    public function update(Request $request)
    {
      try {
        if (Auth::user()->can('Administrar Operativos')) {    
          $operativo = Operativo::findOrFail($request->input('operativo_id'));
          session(['operativo_en_edicion' => $operativo]);
            Validator::make($request->all(), [
                'nombre' => 'required|string|max:255|unique:operativo',
                'observacion' => 'nullable|string|max:255'
            ])->validateWithBag('edit');

            $operativo->nombre = $request->nombre;
            $operativo->observacion = $request->observacion;
            $operativo->save();

            flash("Operativo actualizado!")->success();
            return redirect()->route('operativos');
        } else {
            flash("No tienes permiso para hacer eso.")->error();
            return back();
        }
      } catch (PermissionDoesNotExist $e) {
          flash('message', 'No existe el permiso "Administrar Geoservicios"')->error();
      }
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
                            $dataJson = htmlspecialchars(json_encode($data), ENT_QUOTES, 'UTF-8');
                            // botón de seleccionar Operativo
                            if($data['seleccionado']==false) {
                              $button = '<button type="button" class="btn_seleccionar btn-sm btn-primary" > Seleccionar </button> ';
                            } else {
                              $button = '<b>Seleccionado</b>';
                            }
                            if (Auth::user()->can('Administrar Operativos')) { 
                              $button .= '<button type="button" class="btn_arch btn-sm btn-warning editButton" data-operativo="'.$dataJson.'"> Editar </button> ';
                              $button .= '<button type="button" class="btn_arch btn-sm btn-danger btn_op_delete" > Eliminar </button>';
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
