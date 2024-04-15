<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Provincia;
use App\Model\Departamento;
use App\Model\Localidad;
use App\Model\Entidad;
use App\Model\Radio;
use App\Model\Operativo;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
	        flash('Cantidad de Provincias cargadas: '.Provincia::count());
          flash('Cantidad de Departamentos/Partidos/Comunas cargados: '.Departamento::count());
          flash('Cantidad de Localidades cargados: '.Localidad::count());
          flash('Cantidad de Entidades cargados: '.Entidad::count());
          flash('Cantidad de Radios cargados: '.Radio::count());
          flash('Cantidad de Radios segmentados: '.Radio::whereNotNull('resultado')->count())->success();
          if (session('operativo')) {
            $operativo_actual = Operativo::hydrate( session('operativo') );
            flash('Operativo seleccionado: '.$operativo_actual->first()->nombre)->warning();
          }
        return view('home');
    }

}
