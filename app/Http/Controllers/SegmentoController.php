<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Segmento;
use Auth;
use Illuminate\Support\Facades\Log;

class SegmentoController extends Controller
{
  /**
   * Mostrar el Segmento
   */
  public function show($segmento, Request $request) //: View
  {
    $oSegmento =  Segmento::findOrNew($segmento);
    $oProvincia = $oSegmento->provincia;

    if ($request->ajax()) {
      return view('segmento.info', [
          'segmento' => $oSegmento
          ,'provincia' => $oProvincia ?? new Provincia (['nombre'=>'No province','id'=>0,'id'=>0])
      ]);
    }else{
      return view('segmento.view', [
        'segmento' => $oSegmento
        ,'provincia' => $oProvincia ?? new Provincia (['nombre'=>'No province','id'=>0,'id'=>0])
      ]);
    }
  }

  public function index()
  {
      $segmentos= '';//Segmento::with(['provincia'])->get();
      return view('segmento.list',['segmentos' => $segmentos]);
  }

  public function segsList()
  {
         // Segs
         $aSegs=[];
         $segsQuery = Segmento::with(['provincia']);
         $id = (!empty($_GET["id"])) ? ($_GET["id"]) : ('');
         if ($id!='') {
            $segsQuery->where('id', '=', $id);
         }
      $qSegs = $segsQuery
              ->orderBy('id','asc') //'nombre'])
              ->get();
      foreach ($qSegs as $seg){
        $aSegs[$seg->id]=['id' => $seg->id,
                          'provincia' => $seg->provincia->nombre,
                          'codprov' => $seg->provincia->codigo,
                          'data' => $seg->toJson(JSON_PRETTY_PRINT),
                          'vivs' => $seg->vivs
                          ];
      }
    return datatables()->of($aSegs)
              ->addColumn('action', function($data){
                  $button = '<button type="button" class="btn_descarga btn-sm btn-primary" > Descargar </button> ';
                  // botón de eliminar Segmento  en test, si esta logueado.
                  if (Auth::check()) {
                          try {
                              if ( ( Auth::user()->hasPermissionTo($data['codprov'], 'filters') and Auth::user()->can('Borrar Segmento') ) )
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
