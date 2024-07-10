<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\MyDB;

use App\Model\Provincia;
use App\Model\Departamento;
use App\Model\Localidad;
use App\Model\Entidad;
use App\Model\Radio;
use App\Model\Operativo;
use App\Model\Aglomerado;
use App\Model\Archivo;
use App\Model\Fraccion;
use App\Model\Geoservicio;
use App\Model\Informe;
use App\Model\Segmento;
use App\Model\TipoRadio;
use App\User;

class TableroController extends Controller
{
    // Primer tablero de informe por provincias.
    // Histograma radios segmentados.
    public function GraficoProvincias(Request $request) {
        $titulo = "Actividad diaria";
        $subtitulo = "Cantidad de radios según última fecha de radio segmentado";
        if ($request->isMethod('post')) {
             $avances = MyDB::getAvancesProv();
             $data = json_encode ($avances);
             return response()->json($avances);
         }else{
             return view('grafico.show',['titulo'=>$titulo,'subtitulo'=>$subtitulo,'url_data'=>'prov']);
         }
    }

    // Segunta tablero de informe por avances.
    // Histograma .
    public function GraficoAvances(Request $request,Provincia $oProv=null) {
        if ($request->isMethod('post')) {
             $avances = MyDB::getAvances();
             $data = json_encode ($avances);
             return response()->json($avances);
         }else{
             return view('grafico.show',['provincia'=>$oProv,'url_data'=>'avances']);
         }
    }

    // Terecer tablero de informe por provincias.
    // Histograma radios segmentadosi acumulado.
    public function GraficoAvance(Request $request) {
        $titulo = "Avance de segmentación por provincia";
        $subtitulo = "Cantidad de radios según última fecha de radio segmentado";
        if ($request->isMethod('post')) {
             $avances = MyDB::getAvanceProvAcum();
             $data = json_encode ($avances);
             return response()->json($avances);
         }else{
             return view('grafico.show',['titulo'=>$titulo,'subtitulo'=>$subtitulo,'url_data'=>'avance','tipo'=>'acumulado']);
         }
    }

    // Cuarto tablero de informe.
    // Estadìsticas de la base de satos.
    // https://taginfo.openstreetmap.org/reports/database_statistics

    public function EstadisticasBD(Request $request) {
      $titulo = "Estadísticas de la base de datos";
      $subtitulo = null;
      flash('Cantidad de Provincias cargadas: '.Provincia::count())->important();
      flash('Cantidad de Departamentos/Partidos/Comunas cargados: '.Departamento::count())->important();
      flash('Cantidad de Localidades cargados: '.Localidad::count())->important();
      flash('Cantidad de Entidades cargados: '.Entidad::count())->important();
      flash('Cantidad de Radios cargados: '.Radio::count())->important();
      flash('Cantidad de Radios segmentados: '.Radio::whereNotNull('resultado')->count())->success()->important();
      flash('Cantidad de Operativos: '.Operativo::count())->important();
      flash('Cantidad de Aglomerados: '.Aglomerado::count())->important();
      flash('Cantidad de Archivos: '.Archivo::count())->important();
      flash('Cantidad de Fracciones: '.Fraccion::count())->important();
      flash('Cantidad de Geoservicios: '.Geoservicio::count())->important();
      flash('Cantidad de Informes: '.Informe::count())->important();
      flash('Cantidad de Segmentos: '.Segmento::count())->important();
      flash('Cantidad de Tipo de Radio: '.TipoRadio::count())->important();
      flash('Cantidad de Usuarios: '.User::count())->important();

      if (session('operativo')) {
        $operativo_actual = Operativo::hydrate( session('operativo') )->first();
        flash('Operativo seleccionado: '.$operativo_actual->nombre)->warning()->important();
      }

      if ($request->isMethod('post')) {
          //TODO: Generar post, no existe la ruta igual.
           $database_statistics = MyDB::getAvanceProvAcum();
           $data = json_encode ($database_statistics);
           return response()->json($database_statistics);
       }else{
           return view('informe.show',['titulo'=>$titulo,'subtitulo'=>$subtitulo,'url_data'=>'database_statistics']);
       }
  }
}
