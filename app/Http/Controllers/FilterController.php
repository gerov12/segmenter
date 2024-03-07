<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Log;
use App\User;
use Auth;

class FilterController extends Controller
{
    public function listarFiltros(){
        $filtros = Permission::where('guard_name', 'filters')->get();
        return view('filters', compact('filtros'));
      }

    public function renombrarFiltro(Request $request, Permission $filter){
    $filtro = Permission::where('id', $filter->id)->where('guard_name', 'filters')->first();
    if($filtro) {
        if ($request->newName) {
            $nuevo = Permission::where('name', $request->newName)->where('guard_name', 'filters')->first();
            if($nuevo) {
                return redirect()->back()->with('error_rename','Ya existe el filtro!')->with('id_error', $filter->id);
            } else {
                $filtro->name = $request->newName;
                $filtro->save();
                return redirect()->route('admin.listarFiltros')->with('info','Filtro actualizado!');
            }
        } else {
            return redirect()->back()->with('error_rename','El nombre del filtro no puede estar vacío.')->with('id_error', $filter->id);
        }
    }
    }

    public function crearFiltro(Request $request){
        if($request->newFilterName){
            $filtro = Permission::where('name', $request->newFilterName)->where('guard_name', 'filters')->first();
            if($filtro) {
                return redirect()->back()->with('error_create','Ya existe el filtro!')->with('id', $filtro->id);
            } else {
                Permission::create(['name' => $request->newFilterName, 'guard_name' => 'filters']);
                return redirect()->route('admin.listarFiltros')->with('info','Filtro creado!');
            }
        } else {
            return redirect()->back()->with('error_create','El nombre del filtro no puede estar vacío.');
        }   
    }

    public function eliminarFiltro(Request $request, $filter) {
        $filtro = Permission::where('id', $filter)->where('guard_name', 'filters')->first();
        $nombre = $filtro->name;
        if (Auth::user()->can('Eliminar Filtros')) {
            // tengo que usar esta consulta ya que spatie no tiene implementado el User::permission(permission_name)->get() para multiples guards
            $users = User::whereHas('permissions', function ($query) use ($filtro) {
                $query->where('name', $filtro->name)->where('guard_name', 'filters');
            })->get();
            // tengo que cambiar el guard del filtro antes de quitarselo a los usuarios y eliminarlo y que spatie no tiene implementadas estas funciones para multiples guards
            $filtro->setAttribute('guard_name', 'web');
            $filtro->save();
            foreach ($users as $user) {
                $user->revokePermissionTo($filtro->name);
            }
            $filtro->delete();
            $respuesta = ['statusCode'=> 200,'message' => 'Se eliminó el filtro "'.$nombre.'"!'];
        } else {
            $respuesta = ['statusCode'=> 304,'message' => 'No tenés permiso para eliminar filtros.'];
        }
        return response()->json($respuesta);
    }
}
